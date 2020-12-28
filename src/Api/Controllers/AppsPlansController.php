<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\NotFoundException;
use Baka\Validation as CanvasValidation;
use Canvas\Models\AppsPlans;
use Canvas\Models\Subscription as CanvasSubscription;
use Canvas\Models\SubscriptionsHistory;
use Phalcon\Cashier\Subscription;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;

class AppsPlansController extends BaseController
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
        if (!$this->userData->hasRole('Default.Admins')) {
            $id = $this->userData->getId();
        }

        $this->userData->can('Apps-plans.update', true);

        $this->model = new AppsPlans();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Update a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function edit($stripeId) : Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesn\'t exist'));
        }

        $this->db->begin();
        $subscription = CanvasSubscription::getActiveSubscription();

        if ($subscription->onTrial()) {
            $subscription->name = $appPlan->name;
            $subscription->stripe_plan = $appPlan->stripe_plan;
        } else {
            $subscription->swap($stripeId);
        }

        //Create new history record for the edited subscription
        SubscriptionsHistory::addRecord($subscription);

        //update the subscription with the plan
        $subscription->apps_plans_id = $appPlan->getId();
        $subscription->updateOrFail();

        //return the new subscription plan
        return $this->response($appPlan);
    }

    /**
     * Cancel a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function delete($stripeId) : Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesn\'t exist'));
        }

        $subscription = CanvasSubscription::getActiveSubscription();

        //if on trial you can cancel without going to stripe
        if (!$subscription->onTrial()) {
            $subscription->cancel();
        }

        $subscription->is_cancelled = 1;
        $subscription->update();

        return $this->response($appPlan);
    }

    /**
     * Reactivate a given subscription.
     *
     * @param string $stripeId
     *
     * @return Response
     */
    public function reactivateSubscription($stripeId) : Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesn\'t exist'));
        }

        $subscription = CanvasSubscription::getActiveSubscription();

        //if on trial you can cancel without going to stripe
        if (!$subscription->onTrial()) {
            $subscription->reactivate();
        }

        $subscription->is_cancelled = 0;
        $subscription->update();

        return $this->response($appPlan);
    }

    /**
     * Update payment method.
     *
     * @param int $id
     *
     * @return Response
     */
    public function updatePaymentMethod(string $id) : Response
    {
        $companyGroup = $this->userData->getDefaultCompany()->getDefaultCompanyGroup();

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

            $companyGroup->createCreditCard([
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc,
                ]
            ]);
        } else {
            $token = $this->request->getPut('card_token');
            $companyGroup->updateDefaultCreditCard($token);
        }

        $address = $this->request->getPut('address', 'string');
        $zipcode = $this->request->getPut('zipcode', 'string');

        //update the default company info
        $this->userData->getDefaultCompany()->address = $address;
        $this->userData->getDefaultCompany()->zipcode = $zipcode;
        $this->userData->getDefaultCompany()->update();

        //Update default payment method with new card.
        $subscription = $companyGroup->subscription();

        //not valid? ok then lets charge the credit card to active your subscription
        if (!$subscription->valid()) {
            $subscription->activate();
        }

        return $this->response('Card could not be updated');
    }
}
