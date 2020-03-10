<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\AppsPlans;
use Stripe\Token as StripeToken;
use Phalcon\Http\Response;
use Stripe\Customer as StripeCustomer;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Http\Exception\NotFoundException;
use Canvas\Http\Exception\UnauthorizedException;
use Canvas\Http\Exception\UnprocessableEntityException;
use Canvas\Models\Subscription as CanvasSubscription;
use Phalcon\Cashier\Subscription;
use Canvas\Models\UserCompanyApps;
use function Canvas\Core\paymentGatewayIsActive;
use Canvas\Validation as CanvasValidation;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 * @property \Phalcon\Db\Adapter\Pdo\Mysql $db
 */
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
        if (!$this->userData->hasRole('Default.Admins') || (int) $id === 0) {
            $id = $this->userData->getId();
        }

        $this->userData->can('Users.Apps-plans');

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
     * @return Response
     */
    public function edit($stripeId) : Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesnt exist'));
        }

        $userSubscription = CanvasSubscription::getActiveForThisApp();

        $this->db->begin();

        $subscription = $this->userData->subscription($userSubscription->name);

        if ($subscription->onTrial()) {
            $subscription->name = $appPlan->name;
            $subscription->stripe_plan = $appPlan->stripe_plan;
        } else {
            $subscription->swap($stripeId);
        }

        //update company app
        $companyApp = UserCompanyApps::getCurrentApp();

        //update the company app to the new plan
        if (is_object($companyApp)) {
            $subscription->name = $stripeId;
            $subscription->save();

            $companyApp->stripe_id = $stripeId;
            $companyApp->subscriptions_id = $subscription->getId();
            if (!$companyApp->update()) {
                $this->db->rollback();
                throw new UnprocessableEntityException((string) current($companyApp->getMessages()));
            }

            //update the subscription with the plan
            $subscription->apps_plans_id = $appPlan->getId();
            if (!$subscription->update()) {
                $this->db->rollback();

                throw new UnprocessableEntityException((string) current($subscription->getMessages()));
            }
        }

        $this->db->commit();

        //return the new subscription plan
        return $this->response($appPlan);
    }

    /**
     * Cancel a given subscription.
     *
     * @param string $stripeId
     * @return Response
     */
    public function delete($stripeId): Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesnt exist'));
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
     * @return Response
     */
    public function reactivateSubscription($stripeId): Response
    {
        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesnt exist'));
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
     * @param integer $id
     * @return Response
     */
    public function updatePaymentMethod(string $id): Response
    {
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

            //Create a new card token
            $token = StripeToken::create([
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc,
                ],
            ], [
                'api_key' => $this->config->stripe->secret
            ])->id;
        } else {
            $token = $this->request->getPut('card_token');
        }

        $address = $this->request->getPut('address', 'string');
        $zipcode = $this->request->getPut('zipcode', 'string');

        //update the default company info
        $this->userData->getDefaultCompany()->address = $address;
        $this->userData->getDefaultCompany()->zipcode = $zipcode;
        $this->userData->getDefaultCompany()->update();

        $customerId = !empty($this->userData->stripe_id) ? $this->userData->stripe_id : $this->userData->getDefaultCompany()->get('payment_gateway_customer_id');

        //Update default payment method with new card.
        $stripeCustomer = $this->userData->updatePaymentMethod($customerId, $token);

        $subscription = CanvasSubscription::getActiveForThisApp();

        //not valid? ok then lets charge the credit card to active your subscription
        if (!$subscription->valid()) {
            $subscription->activate();
        }

        if (is_object($stripeCustomer) && $stripeCustomer instanceof StripeCustomer) {
            return $this->response($subscription);
        }
        return $this->response('Card could not be updated');
    }
}
