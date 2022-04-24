<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Http\Exception\NotFoundException;
use Canvas\Models\AppsPlans;
use Canvas\Models\Subscription;
use Phalcon\Http\Response;

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

        $appPlan = AppsPlans::findFirstByStripeId($request['stripe_id']);

        if (!is_object($appPlan)) {
            throw new NotFoundException(_('This plan doesn\'t exist'));
        }

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

        //if on trial you can cancel without going to stripe
        if (!$subscription->onTrial()) {
            $subscription->cancel();
        }

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

        //if on trial you can cancel without going to stripe
        if (!$subscription->onTrial()) {
            $subscription->reactivate();
        }

        $subscription->is_cancelled = 0;
        $subscription->update();

        return $this->response($subscription);
    }
}
