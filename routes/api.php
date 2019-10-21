<?php

use Baka\Router\RouteGroup;
use Baka\Router\Route;

$publicRoutes = [
    Route::get('/')->controller('IndexController'),
    Route::post('/auth')->controller('AuthController')->action('login'),
    Route::post('/refresh')->controller('AuthController')->action('refresh'),
    Route::post('/users')->controller('AuthController')->action('signup'),
    Route::post('/auth/forgot')->controller('AuthController')->action('recover'),
    Route::post('/auth/reset/{key}')->controller('AuthController')->action('reset'),
    Route::get('/users-invite/validate/{hash}')->controller('UsersInviteController')->action('getByHash'),
    Route::post('/users-invite/{hash}')->controller('UsersInviteController')->action('processUserInvite'),
    Route::post('/webhook/payments')->controller('PaymentsController')->action('handleWebhook'),
    Route::get('/apps/{key}/settings')->controller('AppsSettingsController')->action('getByKey'),
    Route::post('/users/social')->controller('AuthController')->action('loginBySocial')
];

$privateRoutes = [
    Route::crud('/users')->notVia('post'),
    Route::crud('/companies'),
    Route::crud('/languages'),
    Route::crud('/webhooks'),
    Route::crud('/filesystem'),
    Route::crud('/roles'),
    Route::crud('/locales'),
    Route::crud('/currencies'),
    Route::crud('/apps'),
    Route::crud('/notifications'),
    Route::crud('/system-modules')->controller('SystemModulesController'),
    Route::crud('/companies-branches')->controller('CompaniesBranchesController'),
    Route::crud('/apps-plans')->controller('AppsPlansController'),
    Route::crud('/roles-acceslist')->controller('RolesAccesListController'),
    Route::crud('/permissions-resources')->controller('PermissionsResourcesController'),
    Route::crud('/permissions-resources-access')->controller('PermissionsResourcesAccessController'),
    Route::crud('/users-invite')->controller('UsersInviteController'),
    Route::crud('/email-templates')->controller('EmailTemplatesController'),
    Route::crud('/companies-custom-fields')->controller('CompaniesCustomFieldsController'),
    Route::crud('/custom-fields-modules')->controller('CustomFieldsModulesController'),
    Route::crud('/custom-fields')->controller('CustomFieldsController'),
    Route::crud('/user-webhooks')->controller('UserWebhooksController'),
    Route::crud('/devices')->controller('UserLinkedSourcesController'),
    Route::crud('/custom-filters')->controller('CustomFiltersController'),
    Route::crud('/email-templates-variables')->controller('EmailTemplatesVariablesController'),
    Route::crud('/templates-variables')->controller('EmailTemplatesVariablesController'),

    Route::get('/timezones')->controller('TimeZonesController'),
    Route::post('/notifications-read-all')->controller('NotificationsController')->action('cleanAll'),
    Route::post('/users/{id}/devices')->controller('UserLinkedSourcesController')->action('devices'),
    Route::delete('/users/{id}/devices/{deviceId}')->controller('UserLinkedSourcesController')->action('detachDevice'),
    Route::delete('/filesystem/{id}/attributes/{name}')->controller('FilesystemController')->action('deleteAttributes'),
    Route::put('/filesystem-entity/{id}')->controller('FilesystemController')->action('editEntity'),
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
    Route::put('/users/{id}/apps/{appsId}/status')->controller('UsersController')->action('changeAppUserActiveStatus')
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
