<?php

use Phinx\Seed\AbstractSeed;

class SubscriptionsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'users_id' => 1,
                'companies_id' => 1,
                'companies_groups_id' => 1,
                'companies_branches_id' => 1,
                'apps_id' => 1,
                'name' => 'example',
                'stripe_id' => 'asaefaeasdafa132eb',
                'stripe_plan' => 'asaefaeasdafa132eb',
                'stripe_status' => 'active',
                'quantity' => 1,
                'is_freetrial' => 1,
                'is_active' => 1,
                'paid' => 1,
                'created_at' => date('Y-m-d H:m:s')
            ]
        ];

        $posts = $this->table('subscriptions');
        $posts->insert($data)
              ->save();
    }
}
