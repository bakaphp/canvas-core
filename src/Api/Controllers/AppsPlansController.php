<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\AppsPlans;
use Canvas\Exception\UnauthorizedHttpException;
use Canvas\Exception\NotFoundHttpException;
use Stripe\Token as StripeToken;
use Phalcon\Http\Response;
use Stripe\Customer as StripeCustomer;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Exception\UnprocessableEntityHttpException;
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
        $this->model = new AppsPlans();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Given the app plan stripe id , subscribe the current company to this aps.
     *
     * @return Response
     */
    public function create(): Response
    {
        if (!$this->userData->hasRole('Default.Admins')) {
            throw new UnauthorizedHttpException(_('You dont have permission to subscribe this apps'));
        }

        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('stripe_id', new PresenceOf(['message' => _('The plan is required.')]));
        $validation->add('card_number', new PresenceOf(['message' => _('Credit Card Number is required.')]));
        $validation->add('card_exp_month', new PresenceOf(['message' => _('Credit Card expiration month is required.')]));
        $validation->add('card_exp_year', new PresenceOf(['message' => _('Credit Card expiration year is required.')]));
        $validation->add('card_cvc', new PresenceOf(['message' => _('CVC is required.')]));

        //validate this form for password
        $messages = $validation->validate($this->request->getPost());
        if (count($messages)) {
            foreach ($messages as $message) {
                throw new UnprocessableEntityHttpException((string) $message);
            }
        }

        $stripeId = $this->request->getPost('stripe_id');
        $company = $this->userData->getDefaultCompany();
        $cardNumber = $this->request->getPost('card_number');
        $expMonth = $this->request->getPost('card_exp_month');
        $expYear = $this->request->getPost('card_exp_year');
        $cvc = $this->request->getPost('card_cvc');

        $appPlan = $this->model->findFirstByStripeId($stripeId);

        if (!is_object($appPlan)) {
            throw new NotFoundHttpException(_('This plan doesnt exist'));
        }

        $userSubscription = Subscription::findFirst([
            'conditions' => 'user_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted  = 0',
            'bind' => [$this->userData->getId(), $this->userData->currentCompanyId(), $this->app->getId()]
        ]);

        //we can only run stripe paymenta gateway if we have the key
        if (paymentGatewayIsActive()) {
            $card = StripeToken::create([
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc,
                ],
            ], [
                'api_key' => $this->config->stripe->secret
            ])->id;

            $this->db->begin();

            if ($appPlan->free_trial_dates == 0) {
                $this->userData->newSubscription($appPlan->name, $appPlan->stripe_id, $company, $this->app)->create($card);
            } else {
                $this->userData->newSubscription($appPlan->name, $appPlan->stripe_id, $company, $this->app)->trialDays($appPlan->free_trial_dates)->create($card);
            }

            //update company app
            $companyApp = UserCompanyApps::getCurrentApp();

            if ($userSubscription) {
                $userSubscription->stripe_id = $this->userData->active_subscription_id;
                if (!$userSubscription->update()) {
                    $this->db->rollback();
                    throw new UnprocessableEntityHttpException((string)current($userSubscription->getMessages()));
                }
            }

            //update the company app to the new plan
            if (is_object($companyApp)) {
                $subscription = $this->userData->subscription($appPlan->stripe_plan);
                $companyApp->stripe_id = $stripeId;
                $companyApp->subscriptions_id = $subscription->getId();
                if (!$companyApp->update()) {
                    $this->db->rollback();
                    throw new UnprocessableEntityHttpException((string)current($companyApp->getMessages()));
                }

                //update the subscription with the plan
                $subscription->apps_plans_id = $appPlan->getId();
                if (!$subscription->update()) {
                    $this->db->rollback();
                    throw new UnprocessableEntityHttpException((string)current($subscription->getMessages()));
                }
            }

            $this->db->commit();
        }

        //sucess
        return $this->response($appPlan);
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
            throw new NotFoundHttpException(_('This plan doesnt exist'));
        }

        /**
         * @todo change the subscription to use getActiveForThisApp , not base on the user
         */
        $userSubscription = Subscription::findFirst([
            'conditions' => 'user_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted  = 0',
            'bind' => [$this->userData->getId(), $this->userData->currentCompanyId(), $this->app->getId()]
        ]);

        if (!is_object($userSubscription)) {
            throw new NotFoundHttpException(_('No current subscription found'));
        }

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
                throw new UnprocessableEntityHttpException((string) current($companyApp->getMessages()));
            }

            //update the subscription with the plan
            $subscription->apps_plans_id = $appPlan->getId();
            if (!$subscription->update()) {
                $this->db->rollback();

                throw new UnprocessableEntityHttpException((string) current($subscription->getMessages()));
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
            throw new NotFoundHttpException(_('This plan doesnt exist'));
        }

        $userSubscription = Subscription::findFirst([
            'conditions' => 'user_id = ?0 and companies_id = ?1 and apps_id = ?2 and is_deleted  = 0',
            'bind' => [$this->userData->getId(), $this->userData->currentCompanyId(), $this->app->getId()]
        ]);

        if (!is_object($userSubscription)) {
            throw new NotFoundHttpException(_('No current subscription found'));
        }

        $subscription = $this->userData->subscription($userSubscription->name);

        //if on trial you can cancel without going to stripe
        if (!$subscription->is_freetrial) {
            $subscription->cancel();
        }

        $subscription->is_cancelled = 1;
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

        $customerId = $this->userData->stripe_id;

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
