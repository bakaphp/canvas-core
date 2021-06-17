<?php

namespace Canvas\Cli\Tasks;

use Baka\Database\Exception\ModelNotProcessedException;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Cashier\Cashier;
use Canvas\Models\Apps;
use Canvas\Models\AppsPlans;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Subscription;
use Canvas\Models\SubscriptionItems;
use Canvas\Models\Users;
use Phalcon\Cli\Task as PhTask;
use Phalcon\Security\Random;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class MainTask extends PhTask
{
    /**
     * Executes the main action of the cli mapping passed parameters to tasks.
     */
    public function mainAction()
    {
        // 'green' => "\033[0;32m(%s)\033[0m",
        // 'red'   => "\033[0;31m(%s)\033[0m",
        $year = date('Y');
        $output = <<<EOF
******************************************************
 Kanvas Team | (C) {$year}
******************************************************

Usage: runCli <command>

  --help         \e[0;32m(safe)\e[0m shows the help screen/available commands
  --clear-cache  \e[0;32m(safe)\e[0m clears the cache folders

EOF;

        echo $output;
    }

    /**
     * Upgrade Kanvas Core version.
     *
     * @return void
     */
    public function upgradeAction() : void
    {
        if ((float) Apps::VERSION === 0.3) {
            $this->version03();
        } else {
            echo 'No Upgrading for this kanvas core' . PHP_EOL;
        }
    }

    /**
     * Upgrade to version 0.3.
     *
     * @return void
     */
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
        $subscriptions = Subscription::find('is_active = 1');

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
                } catch (ModelNotProcessedException | InternalServerErrorException $e) {
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
                } catch (ApiErrorException $e) {
                    $subscription->is_active = 0;
                    $subscription->updateOrFail();
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        }
    }

    /**
     * upgradeAddUuidToUsers.
     *
     * @return void
     */
    public function upgradeAddUuidToUsers() : void
    {
        $random = new Random();
        $users = Users::find();
        foreach ($users as $user) {
            if (!$user->uuid) {
                $user->uuid = $random->uuid();
                $user->saveOrFail();
            }
        }
    }
}
