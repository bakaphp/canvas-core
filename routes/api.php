<?php
use Baka\Http\RouterCollection;

/**
 * @todo how can we better define the version across the diff apps?
 * Here is where you can register all of the routes for api.
 */
$router = new RouterCollection($application);
$router->setPrefix('/v1');

$router->get('/timezones', [
    'Canvas\Api\Controllers\TimeZonesController',
    'index',
]);

/**
 * Authentification Calls.
 * @var [type]
 */
$router->post('/auth', [
    'Canvas\Api\Controllers\AuthController',
    'login',
    'options' => [
        'jwt' => false,
    ]
]);

//asociate mobile devices
$router->post('/users/{id}/devices', [
    'Canvas\Api\Controllers\UserLinkedSourcesController',
    'devices',
]);

//detach mobile devices
$router->post('/users/{id}/devices/{deviceId}/detach', [
    'Canvas\Api\Controllers\UserLinkedSourcesController',
    'detachDevice',
]);

/**
 * Need to understand if using this can be a performance disadvantage in the future.
 */
$defaultCrudRoutes = [
    'users',
    'companies',
    'CompaniesBranches' => 'companies-branches',
    'languages',
    'AppsPlans' => 'apps-plans',
    'RolesAccesList' => 'roles-acceslist',
    'PermissionsResources' => 'permissions-resources',
    'PermissionsResourcesAccess' => 'permissions-resources-access',
    'UsersInvite' => 'users-invite',
    'EmailTemplates' => 'email-templates',
    'CompaniesCustomFields' => 'companies-custom-fields',
    'CustomFieldsModules' => 'custom-fields-modules',
    'CustomFields' => 'custom-fields',
    'EmailTemplatesVariables' => 'templates-variables',
    'webhooks',
    'filesystem',
    'UserWebhooks' => 'user-webhooks',
    'roles',
    'locales',
    'currencies',
    'UserLinkedSources' => 'devices',
    'PaymentFrequencies' => 'payment-frequencies',
    'SystemModules' => 'system-modules',
    'apps'
];

foreach ($defaultCrudRoutes as $key => $route) {
    //set the controller name
    $name = is_int($key) ? $route : $key;
    $controllerName = ucfirst($name) . 'Controller';

    $router->get('/' . $route, [
        'Canvas\Api\Controllers\\' . $controllerName,
        'index',
    ]);

    $router->post('/' . $route, [
        'Canvas\Api\Controllers\\' . $controllerName,
        'create',
    ]);

    $router->get('/' . $route . '/{id}', [
        'Canvas\Api\Controllers\\' . $controllerName,
        'getById',
    ]);

    $router->put('/' . $route . '/{id}', [
        'Canvas\Api\Controllers\\' . $controllerName,
        'edit',
    ]);

    $router->put('/' . $route, [
        'Canvas\Api\Controllers\\' . $controllerName,
        'multipleUpdates',
    ]);

    $router->delete('/' . $route . '/{id}', [
        'Canvas\Api\Controllers\\' . $controllerName,
        'delete',
    ]);
}

//detach mobile devices
$router->delete('/filesystem/{id}/attributes/{name}', [
    'Canvas\Api\Controllers\FilesystemController',
    'deleteAttributes',
]);

//handle upload files from uptty
$router->post('/filesystem-uppy', [
    'Canvas\Api\Controllers\FilesystemController',
    'createUppy',
]);

$router->post('/users', [
    'Canvas\Api\Controllers\AuthController',
    'signup',
    'options' => [
        'jwt' => false,
    ]
]);

$router->put('/auth/logout', [
    'Canvas\Api\Controllers\AuthController',
    'logout',
]);

$router->post('/auth/forgot', [
    'Canvas\Api\Controllers\AuthController',
    'recover',
    'options' => [
        'jwt' => false,
    ]
]);

$router->post('/roles-acceslist/{id}/copy', [
    'Canvas\Api\Controllers\RolesAccesListController',
    'copy'
]);

$router->post('/auth/reset/{key}', [
    'Canvas\Api\Controllers\AuthController',
    'reset',
    'options' => [
        'jwt' => false,
    ]
]);

$router->post('/users/invite', [
    'Canvas\Api\Controllers\UsersInviteController',
    'insertInvite'
]);

$router->post('/users-invite/{hash}', [
    'Canvas\Api\Controllers\UsersInviteController',
    'processUserInvite',
    // 'options' => [
    //     'jwt' => false,
    // ]
]);

$router->get('/users-invite/validate/{hash}', [
    'Canvas\Api\Controllers\UsersInviteController',
    'getByHash',
    'options' => [
        'jwt' => false,
    ]
]);

//Custom Fields specific routes
$router->get('/custom-fields-modules/{id}/fields', [
    'Canvas\Api\Controllers\CustomFieldsModulesController',
    'customFieldsByModulesId',
]);

$router->post('/webhook/payments', [
    'Canvas\Api\Controllers\PaymentsController',
    'handleWebhook',
    'options' => [
        'jwt' => false,
    ]
]);

// Email Template Copy
$router->post('/email-templates/{id}/copy', [
    'Canvas\Api\Controllers\EmailTemplatesController',
    'copy',
]);

$router->post('/email-templates/test', [
    'Canvas\Api\Controllers\EmailTemplatesController',
    'sendTestEmail',
]);

$router->put('/apps-plans/{id}/method', [
    'Canvas\Api\Controllers\AppsPlansController',
    'updatePaymentMethod',
]);

$router->post('/users/{hash}/change-email', [
    'Canvas\Api\Controllers\AuthController',
    'changeUserEmail',
]);

$router->post('/users/{id}/request-email-change', [
    'Canvas\Api\Controllers\AuthController',
    'sendEmailChange',
]);

$router->mount();
