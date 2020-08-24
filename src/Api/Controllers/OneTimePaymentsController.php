<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Phalcon\Http\Response;
use Stripe\StripeClient;

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
class OneTimePaymentsController extends BaseController
{
    /**
     * Create a new Apple Pay Intent
     *
     * @return Response
     */
    public function createPaymentIntent(): Response
    {
        $request = $this->request->getPostData();

        $stripe = new StripeClient(getenv('STRIPE_SECRET'));

        $intent = $stripe->paymentIntents->create([
            'amount' => $request['amount'],
            'currency' => 'usd',
            'receipt_email' => $this->userData->email,
            'customer' => $this->userData->stripe_id,
            'payment_method_types' => ['card'],
            'setup_future_usage' => 'on_session'
        ]);
        
        return $this->response($intent->id);
    }

    /**
     * Confirm Apple Pay Payment
     *
     * @param string $intentId
     * @return Response
     */
    public function confirmPaymentIntent(string $intentId): Response
    {
        $stripe = new StripeClient(getenv('STRIPE_SECRET'));

        $response = $stripe->paymentIntents->confirm(
            $intentId,
            ['payment_method' => 'pm_card_visa']
        );

        return $this->response($response);
    }
}
