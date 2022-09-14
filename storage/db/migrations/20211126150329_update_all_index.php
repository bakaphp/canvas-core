<?php

use Phinx\Db\Adapter\MysqlAdapter;

class UpdateAllIndex extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('countries_cities', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['states_id'], [
                'name' => 'states_id',
                'unique' => false,
            ])
            ->addIndex(['countries_id'], [
                'name' => 'countries_id',
                'unique' => false,
            ])
            ->addIndex(['latitude'], [
                'name' => 'latitude',
                'unique' => false,
            ])
            ->addIndex(['longitude'], [
                'name' => 'longitude',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('countries_states', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['countries_id'], [
                'name' => 'countries_id',
                'unique' => false,
            ])
            ->addIndex(['code'], [
                'name' => 'code',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('banlist', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_bin',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['email'], [
                'name' => 'email',
                'unique' => false,
            ])
            ->save();
        $this->table('users_notification_settings', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'notifications_types_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('notifications_importance', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('users_notification_entity_importance', [
            'id' => false,
            'primary_key' => ['apps_id', 'users_id', 'entity_id', 'system_modules_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_mysql500_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('audits', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['is_cancelled'], [
                'name' => 'is_cancelled',
                'unique' => false,
            ])
            ->save();
        $this->table('payment_frequencies', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('apps_keys', [
            'id' => false,
            'primary_key' => ['apps_id', 'users_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('access_list', [
            'id' => false,
            'primary_key' => ['resources_name', 'access_name', 'roles_id', 'apps_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['allowed'], [
                'name' => 'allowed',
                'unique' => false,
            ])
            ->addIndex(['roles_name'], [
                'name' => 'roles_name',
                'unique' => false,
            ])
            ->save();
        $this->table('roles', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_default'], [
                'name' => 'is_default',
                'unique' => false,
            ])
            ->addIndex(['is_active'], [
                'name' => 'is_active',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('notifications_unsubscribe', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('countries', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['code'], [
                'name' => 'code',
                'unique' => false,
            ])
            ->addIndex(['flag'], [
                'name' => 'flag',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('filesystem_entities', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('sessions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['is_admin'], [
                'name' => 'is_admin',
                'unique' => false,
            ])
            ->save();
        $this->table('apps', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('apps_settings', [
            'id' => false,
            'primary_key' => ['apps_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('sources', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['language_id'], [
                'name' => 'language_id',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('users_associated_apps', [
            'id' => false,
            'primary_key' => ['users_id', 'apps_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['user_active'], [
                'name' => 'user_active',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
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
            ->addIndex(['entity_id'], [
                'name' => 'entity_id',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('users_associated_company', [
            'id' => false,
            'primary_key' => ['users_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['user_active'], [
                'name' => 'user_active',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('companies_branches', [
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
            ->addIndex(['zipcode'], [
                'name' => 'zipcode',
                'unique' => false,
            ])
            ->addIndex(['created_at', 'updated_at', 'is_deleted'], [
                'name' => 'created_at_updated_at_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['email'], [
                'name' => 'email',
                'unique' => false,
            ])
            ->save();
        $this->table('companies', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '					',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('uuid', 'char', [
                'null' => true,
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->changeColumn('name', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'uuid',
            ])
            ->changeColumn('profile_image', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->changeColumn('website', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_TINY,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_image',
            ])
            ->changeColumn('address', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_TINY,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'website',
            ])
            ->changeColumn('zipcode', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address',
            ])
            ->changeColumn('email', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'zipcode',
            ])
            ->changeColumn('language', 'string', [
                'null' => true,
                'limit' => 3,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->changeColumn('timezone', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'language',
            ])
            ->changeColumn('phone', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'timezone',
            ])
            ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'after' => 'phone',
            ])
            ->changeColumn('has_activities', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_id',
            ])
            ->changeColumn('currency_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'has_activities',
            ])
            ->changeColumn('system_modules_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'currency_id',
            ])
            ->changeColumn('country_code', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'country_code',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => true,
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->addIndex(['email'], [
                'name' => 'email',
                'unique' => false,
            ])
            ->addIndex(['language'], [
                'name' => 'language',
                'unique' => false,
            ])
            ->addIndex(['timezone'], [
                'name' => 'timezone',
                'unique' => false,
            ])
            ->addIndex(['has_activities'], [
                'name' => 'has_activities',
                'unique' => false,
            ])
            ->addIndex(['currency_id'], [
                'name' => 'currency_id',
                'unique' => false,
            ])
            ->addIndex(['system_modules_id'], [
                'name' => 'system_modules_id',
                'unique' => false,
            ])
            ->addIndex(['country_code'], [
                'name' => 'country_code',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['uuid'], [
                'name' => 'uuid',
                'unique' => false,
            ])
            ->save();
        $this->table('filesystem_settings', [
            'id' => false,
            'primary_key' => ['filesystem_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('menus', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['slug'], [
                'name' => 'slug',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('filesystem', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->save();
        $this->table('apps_plans_settings', [
            'id' => false,
            'primary_key' => ['apps_plans_id', 'apps_id', 'key'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
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
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('uuid', 'char', [
                'null' => true,
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->changeColumn('status', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => '3',
                'after' => 'system_modules_id',
            ])
            ->changeColumn('address_1', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->changeColumn('address_2', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address_1',
            ])
            ->changeColumn('zip_code', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address_2',
            ])
            ->changeColumn('user_recover_code', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'zip_code',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'user_recover_code',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->addIndex(['uuid'], [
                'name' => 'uuid',
                'unique' => false,
            ])
            ->addIndex(['city_id'], [
                'name' => 'city_id',
                'unique' => false,
            ])
            ->addIndex(['state_id'], [
                'name' => 'state_id',
                'unique' => false,
            ])
            ->addIndex(['country_id'], [
                'name' => 'country_id',
                'unique' => false,
            ])
            ->addIndex(['registered'], [
                'name' => 'registered',
                'unique' => false,
            ])
            ->addIndex(['lastvisit'], [
                'name' => 'lastvisit',
                'unique' => false,
            ])
            ->addIndex(['dob'], [
                'name' => 'dob',
                'unique' => false,
            ])
            ->addIndex(['timezone'], [
                'name' => 'timezone',
                'unique' => false,
            ])
            ->addIndex(['user_login_tries'], [
                'name' => 'user_login_tries',
                'unique' => false,
            ])
            ->addIndex(['user_last_login_try'], [
                'name' => 'user_last_login_try',
                'unique' => false,
            ])
            ->addIndex(['session_time'], [
                'name' => 'session_time',
                'unique' => false,
            ])
            ->addIndex(['session_page'], [
                'name' => 'session_page',
                'unique' => false,
            ])
            ->addIndex(['welcome'], [
                'name' => 'welcome',
                'unique' => false,
            ])
            ->addIndex(['user_activation_key'], [
                'name' => 'user_activation_key',
                'unique' => false,
            ])
            ->addIndex(['user_activation_email'], [
                'name' => 'user_activation_email',
                'unique' => false,
            ])
            ->addIndex(['user_activation_forgot'], [
                'name' => 'user_activation_forgot',
                'unique' => false,
            ])
            ->addIndex(['banned'], [
                'name' => 'banned',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['status'], [
                'name' => 'status',
                'unique' => false,
            ])
            ->addIndex(['user_recover_code'], [
                'name' => 'user_recover_code',
                'unique' => false,
            ])
            ->save();
        $this->table('companies_associations', [
            'id' => false,
            'primary_key' => ['companies_groups_id', 'companies_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('resources', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('menus_links', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['menus_id'], [
                'name' => 'menus_id',
                'unique' => false,
            ])
            ->addIndex(['parent_id'], [
                'name' => 'parent_id',
                'unique' => false,
            ])
            ->addIndex(['system_modules_id'], [
                'name' => 'system_modules_id',
                'unique' => false,
            ])
            ->addIndex(['position'], [
                'name' => 'position',
                'unique' => false,
            ])
            ->addIndex(['is_published'], [
                'name' => 'is_published',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('apps_plans', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['payment_frequencies_id'], [
                'name' => 'payment_frequencies_id',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['free_trial_dates'], [
                'name' => 'free_trial_dates',
                'unique' => false,
            ])
            ->save();
        $this->table('system_modules_forms', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('register_roles', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('uuid', 'char', [
                'null' => false,
                'default' => '',
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addIndex(['uuid'], [
                'name' => 'uuid',
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
            ->addIndex(['roles_id'], [
                'name' => 'roles_id',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('user_company_apps_activities', [
            'id' => false,
            'primary_key' => ['companies_id', 'company_branches_id', 'apps_id', 'key'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'what are the uses doing in the current app they are using?',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('payment_methods_credentials', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['is_default'], [
                'name' => 'is_default',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('user_roles', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('resources_accesses', [
            'id' => false,
            'primary_key' => ['resources_id', 'resources_name', 'access_name', 'apps_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('subscription_items', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('email_templates', [
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('apps_roles', [
            'id' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->save();
        $this->table('currencies', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['country'], [
                'name' => 'country',
                'unique' => false,
            ])
            ->addIndex(['currency'], [
                'name' => 'currency',
                'unique' => false,
            ])
            ->addIndex(['code'], [
                'name' => 'code',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('payment_methods', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('email_templates_variables', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('name', 'char', [
                'null' => false,
                'default' => '',
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['system_modules_id'], [
                'name' => 'system_modules_id',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('roles_inherits', [
            'id' => false,
            'primary_key' => ['roles_name', 'roles_inherit'],
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
        $this->table('companies_custom_fields', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at', 'updated_at', 'is_deleted'], [
                'name' => 'created_at_updated_at_is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('user_linked_sources', [
            'id' => false,
            'primary_key' => ['users_id', 'source_id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('companies_groups', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('uuid', 'char', [
                'null' => true,
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->changeColumn('name', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'uuid',
            ])
            ->changeColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'name',
            ])
            ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->changeColumn('stripe_id', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->changeColumn('is_default', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'stripe_id',
            ])
            ->changeColumn('country_code', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_default',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'country_code',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addIndex(['country_code', 'created_at', 'updated_at'], [
                'name' => 'country_code_created_at_updated_at',
                'unique' => false,
            ])
            ->addIndex(['uuid'], [
                'name' => 'uuid',
                'unique' => false,
            ])
            ->save();
        $this->table('notification_types', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->save();
        $this->table('languages', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
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
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
        $this->table('locales', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['updated_at'], [
                'name' => 'updated_at',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->save();
    }
}
