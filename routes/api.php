<?php

use function Baka\envValue;
use Baka\Router\Route;
use Baka\Router\RouteGroup;

$publicRoutes = [
    Route::get('/')->controller('IndexController'),
    Route::post('/auth')->controller('AuthController')->action('login'),
    Route::post('/refresh-token')->controller('AuthController')->action('refresh'),
    Route::post('/users')->controller('AuthController')->action('signup'),
    Route::post('/auth/forgot')->controller('AuthController')->action('recover'),
    Route::post('/auth/reset/{key}')->controller('AuthController')->action('reset'),
    Route::get('/users-invite/validate/{hash}')->controller('UsersInviteController')->action('getByHash'),
    Route::post('/users-invite/{hash}')->controller('UsersInviteController')->action('processUserInvite'),
    Route::post('/webhook/payments')->controller('PaymentsController')->action('handleWebhook'),
    Route::get('/apps/{key}/settings')->controller('AppsSettingsController')->action('getByKey'),
    Route::post('/users/social')->controller('AuthController')->action('loginBySocial'),
    Route::get('/countries')->controller('CountriesController')->action('index'),
    Route::get('/countries/{id}')->controller('CountriesController')->action('getById'),
];

$privateRoutes = [
    Route::crud('/apps-keys')->controller('AppsKeysController'),
    Route::post('/apps-keys/regenerate')->controller('AppsKeysController')->action('regenerateKeys'),
    Route::crud('/users')->notVia('post'),
    Route::crud('/companies'),
    Route::crud('/roles'),
    Route::crud('/locales'),
    Route::crud('/currencies'),
    Route::crud('/apps'),
    Route::crud('/notifications'),
    Route::crud('/system-modules')->controller('SystemModulesController'),
    Route::crud('/companies-branches')->controller('CompaniesBranchesController'),
    Route::crud('/apps-plans')->controller('AppsPlansController'),
    Route::post('/apps-plans/{id}/reactivate')->controller('AppsPlansController')->action('reactivateSubscription'),
    Route::crud('/roles-acceslist')->controller('RolesAccessListController'),
    Route::crud('/roles-accesslist')->controller('RolesAccessListController'),
    Route::crud('/permissions-resources')->controller('PermissionsResourcesController'),
    Route::crud('/permissions-resources-access')->controller('PermissionsResourcesAccessController'),
    Route::crud('/users-invite')->controller('UsersInviteController'),
    Route::crud('/devices')->controller('UserLinkedSourcesController'),
    Route::crud('/languages'),
    Route::crud('/webhooks'),
    Route::crud('/filesystem'),
    Route::crud('/custom-fields-types')->controller('CustomFieldsTypesController'),
    Route::crud('/custom-fields-values')->controller('CustomFieldsValuesController'),

    Route::get('/timezones')->controller('TimeZonesController'),
    Route::post('/notifications-read-all')->controller('NotificationsController')->action('cleanAll'),
    Route::post('/users/{id}/devices')->controller('UserLinkedSourcesController')->action('devices'),
    Route::delete('/users/{id}/devices/{deviceId}')->controller('UserLinkedSourcesController')->action('detachDevice'),
    Route::delete('/filesystem/{id}/attributes/{name}')->controller('FilesystemController')->action('deleteAttributes'),
    Route::put('/filesystem-entity/{id}')->controller('FilesystemController')->action('editEntity'),
    Route::crud('/filesystem-entity')->controller('FilesystemEntitiesController'),
    Route::put('/auth/logout')->controller('AuthController')->action('logout'),
    Route::post('/users/invite')->controller('UsersInviteController')->action('insertInvite'),
    Route::post('/roles-acceslist/{id}/copy')->controller('RolesAccessListController')->action('copy'),
    Route::post('/roles-accesslist/{id}/copy')->controller('RolesAccessListController')->action('copy'),
    Route::get('/custom-fields-modules/{id}/fields')->controller('CustomFieldsModulesController')->action('customFieldsByModulesId'),
    Route::put('/apps-plans/{id}/method')->controller('AppsPlansController')->action('updatePaymentMethod'),
    Route::get('/apps-plans/{id}/method')->controller('PaymentMethodsCredsController')->action('getCurrentPaymentMethodsCreds'),
    Route::get('/schema/{slug}')->controller('SchemaController')->action('getBySlug'),
    Route::get('/schema/{slug}/description')->controller('SchemaController')->action('getModelDescription'),
    Route::post('/users/{hash}/change-email')->controller('AuthController')->action('changeUserEmail'),
    Route::post('/users/{id}/request-email-change')->controller('AuthController')->action('sendEmailChange'),
    Route::put('/users/{id}/apps/{appsId}/status')->controller('UsersController')->action('changeAppUserActiveStatus'),
    Route::get('/companies-groups')->controller('CompaniesGroupsController')->action('index'),
    Route::get('/companies-groups/{id}')->controller('CompaniesGroupsController')->action('getById'),
    Route::crud('/users-roles')->controller('UserRolesController'),
    Route::crud('/subscriptions')->controller('SubscriptionsController'),
    Route::crud('/users-associated-apps')->controller('UsersAssociatedAppsController'),
    Route::crud('/users-linked-sources')->controller('UserLinkedSourcesController'),
    Route::crud('/users-config')->controller('UserConfigController'),
    Route::crud('/sessions'),
    Route::crud('/companies-settings')->controller('CompaniesSettingsController'),
    Route::crud('/users-associated-companies')->controller('UsersAssociatedCompaniesController'),
    Route::crud('/users-companies-apps')->controller('UserCompanyAppsController'),
    Route::crud('/companies-associations')->controller('CompaniesAssociationsController'),
    Route::crud('/custom-forms')->controller('SystemModulesFormsController'),
    Route::get('/custom-forms/{slug}')->controller('SystemModulesFormsController')->action('getBySlug'),
    Route::crud('/menus')->controller('MenusController'),
    Route::get('/menus/{slug}')->controller('MenusController')->action('getBySlug'),
    Route::post('/menus/{menusId}/links')->controller('MenusLinksController')->action('create'),
    Route::get('/menus/{menusId}/links')->controller('MenusLinksController')->action('index'),
    Route::get('/menus/{menusId}/links/{id}')->controller('MenusLinksController')->action('getById'),
    Route::put('/menus/{menusId}/links/{id}')->controller('MenusLinksController')->action('edit'),
    Route::delete('/menus/{menusId}/links/{id}')->controller('MenusLinksController')->action('delete'),
    Route::crud('/menus-links')->controller('MenusLinksController'),
    Route::post('/payments')->controller('OneTimePaymentsController')->action('createPaymentIntent'),
    Route::get('/payments/{intentId}/confirm')->controller('OneTimePaymentsController')->action('confirmPaymentIntent'),

];

$privateSubscriptionRoutes = [
    Route::crud('/email-templates')->controller('EmailTemplatesController'),
    Route::crud('/companies-custom-fields')->controller('CompaniesCustomFieldsController'),
    Route::crud('/custom-fields-modules')->controller('CustomFieldsModulesController'),
    Route::crud('/custom-fields')->controller('CustomFieldsController'),
    Route::crud('/user-webhooks')->controller('UserWebhooksController'),
    Route::crud('/custom-filters')->controller('CustomFiltersController'),
    Route::crud('/email-templates-variables')->controller('EmailTemplatesVariablesController'),
    Route::crud('/templates-variables')->controller('EmailTemplatesVariablesController'),

    Route::post('/email-templates/{id}/copy')->controller('EmailTemplatesController')->action('copy'),
    Route::post('/email-templates/test')->controller('EmailTemplatesController')->action('sendTestEmail'),
    Route::post('/user-webhooks/{name}/run')->controller('UserWebhooksController')->action('execute'),
    Route::post('/user-webhooks/{name}/test')->controller('UserWebhooksController')->action('execute'),
];

$publicRoutesGroup = RouteGroup::from($publicRoutes)
                ->defaultNamespace('Canvas\Api\Controllers')
                ->defaultPrefix(envValue('API_VERSION', '/v1'));

$privateRoutesGroup = RouteGroup::from($privateRoutes)
                ->defaultNamespace('Canvas\Api\Controllers')
                ->addMiddlewares('auth.jwt@before', 'auth.acl@before')
                ->defaultPrefix(envValue('API_VERSION', '/v1'));

$subscriptionPrivateRoutes = RouteGroup::from($privateSubscriptionRoutes)
                ->defaultNamespace('Canvas\Api\Controllers')
                ->addMiddlewares('auth.jwt@before', 'auth.acl@before', 'auth.subscription@before')
                ->defaultPrefix(envValue('API_VERSION', '/v1'));

/**
 * @todo look for a better way to handle this
 */
return array_merge(
    $publicRoutesGroup->toCollections(),
    $privateRoutesGroup->toCollections(),
    $subscriptionPrivateRoutes->toCollections()
);
