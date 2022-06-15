<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Validation as CanvasValidation;
use Canvas\Models\AppsPlans;
use Canvas\Models\Subscription;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;
use Stripe\Invoice;
use Stripe\Stripe;

class SubscriptionsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Subscription();

        //get the list of roes for the system + my company
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }

    /**
     * Update a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function update(int $id) : Response
    {
        $this->request->validate([
            'stripe_id' => 'required|string',
        ]);

        $request = $this->request->getPutData();

        $appPlan = AppsPlans::findFirstOrFail([
            'conditions' => 'stripe_id = :stripe_id: AND apps_id = :apps_id:',
            'bind' => [
                'stripe_id' => $request['stripe_id'],
                'apps_id' => $this->app->getId(),
            ],
        ]);

        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscription->name = $appPlan->name;
        $subscription->stripe_plan = $appPlan->stripe_plan;
        $subscription->swap($appPlan);

        //update the subscription with the plan
        $subscription->apps_plans_id = $appPlan->getId();
        $subscription->updateOrFail();

        //return the new subscription plan
        return $this->response($subscription);
    }

    /**
     * Cancel a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function cancel(int $id) : Response
    {
        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscription->cancel();
        $subscription->is_cancelled = 1;
        $subscription->update();

        return $this->response($subscription);
    }

    /**
     * Reactivate a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function reactivate(int $id) : Response
    {
        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscription->reactivate();
        $subscription->is_cancelled = 0;
        $subscription->update();

        return $this->response($subscription);
    }

    /**
     * Update payment method.
     *
     * @param int $id
     *
     * @return Response
     */
    public function updatePaymentMethod(int $id) : Response
    {
        //Update default payment method with new card.
        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscriber = $subscription->getSubscriberEntity();

        if (empty($this->request->hasPut('card_token'))) {
            $validation = new CanvasValidation();
            $validation->add('card_number', new PresenceOf(['message' => _('Credit Card Number is required.')]));
            $validation->add('card_exp_month', new PresenceOf(['message' => _('Credit Card expiration month is required.')]));
            $validation->add('card_exp_year', new PresenceOf(['message' => _('Credit Card expiration year is required.')]));
            $validation->add('card_cvc', new PresenceOf(['message' => _('CVC is required.')]));

            //validate this form for password
            $validation->validate($this->request->getPut());

            $cardNumber = $this->request->getPut('card_number', 'string');
            $expMonth = $this->request->getPut('card_exp_month', 'string');
            $expYear = $this->request->getPut('card_exp_year', 'string');
            $cvc = $this->request->getPut('card_cvc', 'string');

            $token = $subscriber->createCreditCard([
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc,
                ]
            ])->id;
        } else {
            $token = $this->request->getPut('card_token');
        }

        $subscriber->updateDefaultCreditCard($token);
        $address = $this->request->getPut('address', 'string');
        $zipcode = $this->request->getPut('zipcode', 'string');

        //update the default company info
        $this->userData->getDefaultCompany()->address = $address;
        $this->userData->getDefaultCompany()->zipcode = $zipcode;
        $this->userData->getDefaultCompany()->update();

        //not valid? ok then lets charge the credit card to active your subscription
        if (!$subscription->valid()) {
            $subscription->activate();
        }

        return $this->response($subscription);
    }

    /**
     * Update payment method.
     *
     * @param int $id
     *
     * @return Response
     */
    public function getPaymentMethod(int $id) : Response
    {
        //Update default payment method with new card.
        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscriber = $subscription->getSubscriberEntity();
        $cardInfo = $subscriber->getStripeCustomerInfo()->sources;

        if ($cardInfo->total_count == 0) {
            return $this->response(['message' => _('No credit card found.')]);
        }

        return $this->response($cardInfo->data[0]);
    }

    /**
     * Update payment method.
     *
     * @param int $id
     *
     * @return Response
     */
    public function transactionHistory(int $id) : Response
    {
        //Update default payment method with new card.
        $subscription = Subscription::findFirstOrFail([
            'conditions' => 'id = :id: AND companies_id = :companies_id: AND is_deleted = 0',
            'bind' => [
                'id' => $id,
                'companies_id' => $this->userData->currentCompanyId(),
            ],
        ]);

        $subscriber = $subscription->getSubscriberEntity();
        $customer = $subscriber->getStripeCustomerInfo();

        Stripe::setApiKey($this->userData->getDefaultCompany()->getStripeKey());

        $invoices = Invoice::all([
            'customer' => $customer->id,
            'limit' => $this->request->getQuery('limit', 'int', 25),
            'starting_after' => $this->request->getQuery('starting_after', 'string', null),
        ]);

        return $this->response($invoices->data);
    }
}
