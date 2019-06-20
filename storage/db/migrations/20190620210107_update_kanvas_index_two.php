<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateKanvasIndexTwo extends AbstractMigration
{
    public function change()
    {
        $this->table('roles', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->save();
        $this->table('subscriptions', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
        ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
        ->addIndex(['apps_plans_id'], [
                'name' => 'apps_plans_id',
                'unique' => false,
            ])
        ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
            ])
        ->addIndex(['stripe_plan'], [
                'name' => 'stripe_plan',
                'unique' => false,
            ])
        ->addIndex(['trial_ends_at'], [
                'name' => 'trial_ends_at',
                'unique' => false,
            ])
        ->addIndex(['is_freetrial'], [
                'name' => 'is_freetrial',
                'unique' => false,
            ])
        ->addIndex(['is_active'], [
                'name' => 'is_active',
                'unique' => false,
            ])
        ->addIndex(['paid'], [
                'name' => 'paid',
                'unique' => false,
            ])
        ->addIndex(['charge_date'], [
                'name' => 'charge_date',
                'unique' => false,
            ])
        ->addIndex(['ends_at'], [
                'name' => 'ends_at',
                'unique' => false,
            ])
            ->save();
        $this->table('system_modules', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'list of modules , user can interact on each of the diff apps',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['slug'], [
                'name' => 'slug',
                'unique' => false,
            ])
        ->addIndex(['model_name'], [
                'name' => 'model_name',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
        ->addIndex(['parents_id'], [
                'name' => 'parents_id',
                'unique' => false,
            ])
        ->addIndex(['menu_order'], [
                'name' => 'menu_order',
                'unique' => false,
            ])
        ->addIndex(['show'], [
                'name' => 'show',
                'unique' => false,
            ])
        ->addIndex(['use_elastic'], [
                'name' => 'use_elastic',
                'unique' => false,
            ])
            ->save();
        $this->table('user_roles', [
                'id' => false,
                'primary_key' => ['users_id', 'apps_id', 'companies_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['roles_id'], [
                'name' => 'roles_id',
                'unique' => false,
            ])
            ->save();
        $this->table('user_company_apps', [
                'id' => false,
                'primary_key' => ['companies_id', 'apps_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->addIndex(['stripe_id'], [
                'name' => 'stripe_id',
                'unique' => false,
            ])
        ->addIndex(['subscriptions_id'], [
                'name' => 'subscriptions_id',
                'unique' => false,
            ])
            ->save();
        $this->table('user_webhooks', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['webhooks_id'], [
                'name' => 'webhooks_id',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
        ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
        ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->save();
        $this->table('notifications', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
        ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
        ->addIndex(['system_module_id'], [
                'name' => 'system_module_id',
                'unique' => false,
            ])
        ->addIndex(['notification_type_id'], [
                'name' => 'notification_type_id',
                'unique' => false,
            ])
            ->save();
        $this->table('webhooks', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['system_modules_id'], [
                'name' => 'system_modules_id',
                'unique' => false,
            ])
        ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->save();
        $this->table('users_invite', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->addIndex(['invite_hash'], [
                'name' => 'invite_hash',
                'unique' => false,
            ])
        ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
        ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
        ->addIndex(['role_id'], [
                'name' => 'role_id',
                'unique' => false,
            ])
        ->addIndex(['app_id'], [
                'name' => 'app_id',
                'unique' => false,
            ])
            ->save();
    }
}
