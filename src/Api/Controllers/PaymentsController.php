<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Phalcon\Cashier\Traits\StripeWebhookHandlersTrait;
use Phalcon\Http\Response;
use Canvas\Models\Users;
use Canvas\Models\EmailTemplates;
use Canvas\Models\Subscription;
use Canvas\Models\CompaniesSettings;
use Phalcon\Di;
use Exception;

/**
 * Class PaymentsController.
 *
 * Class to handle payment webhook from our cashier library
 *
 * @package Canvas\Api\Controllers
 * @property Log $log
 * @property App $app
 *
 */
class PaymentsController extends BaseController
{
    /**
     * Stripe Webhook Handlers.
     */
    use StripeWebhookHandlersTrait;

    /**
     * Handle stripe webhoook calls.
     *
     * @return Response
     */
    public function handleWebhook(): Response
    {
        //we cant processs if we dont find the stripe header
        if (!$this->request->hasHeader('Stripe-Signature')) {
            throw new Exception('Route not found for this call');
        }
        $request = $this->request->getPostData();
        $type = str_replace('.', '', ucwords(str_replace('_', '', $request['type']), '.'));
        $method = 'handle' . $type;
        $payloadContent = json_encode($request);
        $this->log->info("Webhook Handler Method: {$method} \n");
        $this->log->info("Payload: {$payloadContent} \n");
        if (method_exists($this, $method)) {
            return $this->{$method}($request, $method);
        } else {
            return $this->response(['Missing Method to Handled']);
        }
    }

    /**
     * Handle customer subscription updated.
     *
     * @param  array $payload
     * @return Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload, string $method): Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload, $method);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle customer subscription cancellation.
     *
     * @param  array $payload
     * @return Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload, string $method): Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //Update current subscription's paid column to false and store date of payment
            $this->updateSubscriptionPaymentStatus($user, $payload);
            $this->sendWebhookResponseEmail($user, $payload, $method);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle customer subscription free trial ending.
     *
     * @param  array $payload
     * @return Response
     */
    protected function handleCustomerSubscriptionTrialwillend(array $payload, string $method): Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload, $method);
            $this->log->info("Email was sent to: {$user->email}\n");
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle sucessfull payment.
     *
     * @param array $payload
     * @return Response
     */
    protected function handleChargeSucceeded(array $payload, string $method): Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //Update current subscription's paid column to true and store date of payment
            $this->updateSubscriptionPaymentStatus($user, $payload);
            $this->sendWebhookResponseEmail($user, $payload, $method);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle bad payment.
     *
     * @param array $payload
     * @return Response
     */
    protected function handleChargeFailed(array $payload, string $method) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->updateSubscriptionPaymentStatus($user, $payload);
            $this->sendWebhookResponseEmail($user, $payload, $method);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Handle pending payments.
     *
     * @param array $payload
     * @return Response
     */
    protected function handleChargePending(array $payload, string $method) : Response
    {
        $user = Users::findFirstByStripeId($payload['data']['object']['customer']);
        if ($user) {
            //We need to send a mail to the user
            $this->sendWebhookResponseEmail($user, $payload, $method);
        }
        return $this->response(['Webhook Handled']);
    }

    /**
     * Send webhook related emails to user.
     * @param Users $user
     * @param array $payload
     * @param string $method
     * @return void
     */
    protected function sendWebhookResponseEmail(Users $user, array $payload, string $method): void
    {
        switch ($method) {
            case 'handleCustomerSubscriptionTrialwillend':
                $templateName = 'users-trial-end';
                break;
            case 'handleCustomerSubscriptionUpdated':
                $templateName = 'users-subscription-updated';
                break;

            case 'handleCustomerSubscriptionDeleted':
                $templateName = 'users-subscription-canceled';
                break;

            case 'handleChargeSucceeded':
                $templateName = 'users-charge-success';
                break;

            case 'handleChargeFailed':
                $templateName = 'users-charge-failed';
                break;

            case 'handleChargePending':
                $templateName = 'users-charge-pending';
                break;

            default:
                break;
        }

        //Search for actual template by templateName
        $emailTemplate = EmailTemplates::getByName($templateName);

        Di::getDefault()->getMail()
            ->to($user->email)
            ->subject('Canvas Payments and Subscriptions')
            ->content($emailTemplate->template)
            ->sendNow();
    }

    /**
     * Updates subscription payment status depending on charge event.
     * @param $user
     * @param $payload
     * @return void
     */
    public function updateSubscriptionPaymentStatus(Users $user, array $payload): void
    {
        $chargeDate = date('Y-m-d H:i:s', $payload['data']['object']['created']);

        //Fetch current user subscription
        $subscription = Subscription::getByDefaultCompany($user);

        if (is_object($subscription)) {
            $subscription->paid = $payload['data']['object']['paid'] ? 1 : 0;
            $subscription->charge_date = $chargeDate;

            $subscription = $subscription->validateByGracePeriod();

            if ($subscription->paid) {
                $subscription->is_freetrial = 0;
                $subscription->trial_ends_days = 0;
            }

            //Paid status is false if plan has been canceled
            if ($payload['data']['object']['status'] == 'canceled') {
                $subscription->paid = 0;
                $subscription->charge_date = null;
            }

            if ($subscription->update()) {
                //Update companies setting
                $paidSetting = CompaniesSettings::findFirst([
                    'conditions' => "companies_id = ?0 and name = 'paid' and is_deleted = 0",
                    'bind' => [$user->getDefaultCompany()->getId()]
                ]);

                $paidSetting->value = (string)$subscription->paid;
                $paidSetting->update();
            }
            $this->log->info("User with id: {$user->id} charged status was {$payload['data']['object']['paid']} \n");
        } else {
            $this->log->error("Subscription not found\n");
        }
    }
}
