<?php

use Baka\Router\RouteGroup;
use Baka\Router\Route;

$publicRoutes = [
    Route::get('/')->controller('IndexController'),
    Route::post('/auth')->controller('AuthController')->action('login'),
    Route::post('/users')->controller('AuthController')->action('signup'),
    Route::post('/auth/forgot')->controller('AuthController')->action('recover'),
    Route::post('/auth/reset/{key}')->controller('AuthController')->action('reset'),
    Route::get('/users-invite/validate/{hash}')->controller('UsersInviteController')->action('getByHash'),
    Route::post('/users-invite/{hash}')->controller('UsersInviteController')->action('processUserInvite'),
    Route::post('/webhook/payments')->controller('PaymentsController')->action('handleWebhook'),
    Router::get('/apps/{key}/settings')->controller('AppsSettingsController')->actions('getByKey')
];

$privateRoutes = [
    Route::add('/companies'),
    Route::add('/languages'),
    Route::add('/webhooks'),
    Route::add('/filesystem'),
    Route::add('/roles'),
    Route::add('/locales'),
    Route::add('/currencies'),
    Route::add('/apps'),
    Route::add('/system-modules'),
    Route::add('/companies-branches')->controller('CompaniesBranchesController'),
    Route::add('/apps-plans')->controller('AppsPlansController'),
    Route::add('/roles-acceslist')->controller('RolesAccesListController'),
    Route::add('/permissions-resources')->controller('PermissionsResourcesController'),
    Route::add('/permissions-resources-accesss')->controller('PermissionsResourcesAccessController'),
    Route::add('/users-invite')->controller('UsersInviteController'),
    Route::add('/email-templates')->controller('EmailTemplatesController'),
    Route::add('/companies-custom-fields')->controller('CompaniesCustomFieldsController'),
    Route::add('/custom-fields-modules')->controller('CustomFieldsModulesController'),
    Route::add('/custom-fields')->controller('CustomFieldsController'),
    Route::add('/user-webhooks')->controller('UserWebhooksController'),
    Route::add('/devices')->controller('UserLinkedSourcesController'),
    Route::add('/custom-filters')->controller('CustomFiltersController'),
    Route::add('/email-templates-variables')->controller('EmailTemplatesVariablesController'),
    Route::add('/templates-variables')->controller('EmailTemplatesVariablesController'),

    
    Route::get('/users')->controller('UsersController')->action('index'),
    Route::get('/users/{id}')->controller('UsersController')->action('getById'),
    Route::put('/users/{id}')->controller('UsersController')->action('edit'),
    Route::delete('/users/{id}')->controller('UsersController')->action('delete'),

    Route::get('/timezones')->controller('TimeZonesController'),
    Route::post('/users/{id}/devices')->controller('UserLinkedSourcesController')->action('devices'),
    Route::delete('/users/{id}/devices/{deviceId}')->controller('UserLinkedSourcesController')->action('detachDevice'),
    Route::post('/users/social')->controller('AuthController')->action('loginByAccessToken'),
    Route::delete('/filesystem/{id}/attributes/{name}')->controller('FilesystemController')->action('deleteAttributes'),
    Route::put('/auth/logout')->controller('AuthController')->action('logout'),
    Route::post('/users/invite')->controller('UsersInviteController')->action('insertInvite'),
    Route::post('/roles-acceslist/{id}/copy')->controller('RolesAccesListController')->action('copy'),
    Route::get('/custom-fields-modules/{id}/fields')->controller('CustomFieldsModulesController')->action('customFieldsByModulesId'),
    Route::post('/email-templates/{id}/copy')->controller('EmailTemplatesController')->action('copy'),
    Route::post('/email-templates/test')->controller('EmailTemplatesController')->action('sendTestEmail'),
    Route::put('/apps-plans/{id}/method')->controller('AppsPlansController')->action('updatePaymentMethod'),
    Route::get('/schema/{slug}')->controller('SchemaController')->action('getBySlug'),
    Route::get('/schema/{slug}/description')->controller('SchemaController')->action('getModelDescription'),
    Route::post('/users/{hash}/change-email')->controller('AuthController')->action('changeUserEmail'),
    Route::post('/users/{id}/request-email-change')->controller('AuthController')->action('sendEmailChange'),
];

$publicRoutesGroup = RouteGroup::from($publicRoutes)
                ->defaultNamespace('Canvas\Api\Controllers')
                ->defaultPrefix('/v1');

$privateRoutesGroup = RouteGroup::from($privateRoutes)
                ->defaultNamespace('Canvas\Api\Controllers')
                ->addMiddlewares('auth.jwt@before', 'auth.acl@before')
                ->defaultPrefix('/v1');

/**
 * @todo look for a better way to handle this
 */
return array_merge($publicRoutesGroup->toCollections(), $privateRoutesGroup->toCollections());
