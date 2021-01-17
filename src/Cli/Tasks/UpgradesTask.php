<?php

namespace Canvas\Cli\Tasks;

use Baka\Database\Exception\ModelNotProcessedException;
use Canvas\Cashier\Cashier;
use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
use Canvas\Models\SubscriptionItems;
use Phalcon\Cli\Task as PhTask;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class UpgradeTask extends PhTask
{
    /**
     * Upgrade Kanvas Core version.
     *
     * @return void
     */
    public function mainAction() : void
    {
        if ((float) Apps::VERSION === 0.3) {
            $this->version03();
        } else {
            echo 'No Upgrading for this kanvas core' . PHP_EOL;
        }
    }

    public function version03() : void
    {
        echo 'Upgrading to ' . Apps::VERSION . PHP_EOL;

        //update company groups
        $companyGroups = CompaniesGroups::find();
        foreach ($companyGroups as $group) {
            $group->is_default = 1;
            $group->updateOrFail();
        }

        //update subscription table company group and create company groups if not found
        $subscriptions = Subscription::find();

        foreach ($subscriptions as $subscription) {
            if (!is_object($subscription->companyGroup)) {
                try {
                    $companyGroup = $subscription->company->getDefaultCompanyGroup();
                    $subscription->companies_groups_id = $companyGroup->getId();
                    $subscription->stripe_status = $subscription->is_active ? 'active' : 'canceled';
                    $subscription->updateOrFail();

                    $stripeSubscription = StripeSubscription::retrieve(
                        $subscription->stripe_id,
                        Cashier::stripeOptions()
                    );

                    $companyGroup->stripe_id = $stripeSubscription->customer;
                    $companyGroup->updateOrFail();

                    foreach ($stripeSubscription->items as $item) {
                        $subscriptionItem = new SubscriptionItems();
                        $subscriptionItem->subscription_id = $subscription->getId();
                        $subscriptionItem->apps_plans_id = AppsPlans::getDefaultPlan()->getId();
                        $subscriptionItem->stripe_id = $item->id;
                        $subscriptionItem->stripe_plan = $item->plan->id;
                        $subscriptionItem->quantity = $item->quantity;
                        $subscriptionItem->saveOrFail();
                    }

                    echo 'Update Subscription ' . $subscription->getId() . PHP_EOL;
                } catch (ApiErrorException $e) {
                    echo $e->getMessage() . PHP_EOL;
                } catch (ModelNotProcessedException $e) {
                    $companiesGroup = CompaniesGroups::findFirstOrCreate([
                        'conditions' => 'apps_id = ?0 and users_id = ?1 and is_deleted = 0',
                        'bind' => [
                            $subscription->apps_id,
                            $subscription->users_id,
                        ]
                    ], [
                        'name' => $subscription->company->name,
                        'apps_id' => $subscription->apps_id,
                        'users_id' => $subscription->users_id,
                        'is_default' => 1
                    ]);

                    $companiesGroup->associate($subscription->company);

                    echo $e->getMessage() . PHP_EOL;
                    echo 'Fail ' . $subscription->getId() . PHP_EOL;
                }
            }
        }
    }
}
