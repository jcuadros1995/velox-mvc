<?php

require_once __DIR__ . '/../bootstrap.php';

$router = new Router();

define(
    'BASE_PATH',
    dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| WEB
|--------------------------------------------------------------------------
*/

$router->get(
    '/',
    'HomeController@index'
);

$router->get(
    '/menu',
    'MenuController@index'
);

$router->get(
    '/sedes',
    'BranchController@publicPage'
);

$router->get(
    '/menu/hamburguesas',
    'MenuController@hamburguesas'
);

$router->get(
    '/menu/{slug}',
    'CategoryController@publicPage'
);

$router->get(
    '/pages/{slug}',
    'PublicPageController@show'
);

$router->get(
    '/eventos',
    'PublicPageController@eventsAlias'
);

$router->post(
    '/api/contact',
    'PublicPageController@contactSubmit'
);

$router->post(
    '/api/events/register',
    'PublicPageController@eventSubmit'
);

$router->get(
    '/login',
    'AuthController@showLogin',
    [
        ['Middleware', 'webGuest', []]
    ]
);

$router->get(
    '/registro',
    'AuthController@showRegister',
    [
        ['Middleware', 'webGuest', []]
    ]
);

$router->get(
    '/recuperar-contrasena',
    'AuthController@showForgotPassword'
);



/*
|--------------------------------------------------------------------------
| AUTH API
|--------------------------------------------------------------------------
*/

$router->post(
    '/api/register',
    'AuthController@register'
);

$router->post(
    '/api/login',
    'AuthController@login'
);

$router->post(
    '/api/logout',
    'AuthController@logout',
    [
        ['Middleware', 'auth', []]
    ]
);

$router->post(
    '/api/forgot-password',
    'AuthController@forgotPassword'
);

$router->get(
    '/restablecer-contrasena',
    'AuthController@showResetPassword'
);

$router->post(
    '/api/reset-password',
    'AuthController@resetPassword'
);



/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/dashboard',
    'AdminController@dashboard',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin', 'staff']]
    ]
);

$router->get(
    '/admin/reservations',
    'AdminReservationController@index',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin', 'staff']]
    ]
);

$router->get(
    '/admin/categories',
    'CategoryController@index',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/admin/categories/create',
    'CategoryController@create',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/admin/categories/edit',
    'CategoryController@edit',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/admin/categories/page',
    'CategoryController@page',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/api/admin/categories',
    'CategoryController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/store',
    'CategoryController@store',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/update',
    'CategoryController@update',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/toggle',
    'CategoryController@toggle',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/page/save',
    'CategoryController@savePage',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/page/images',
    'CategoryController@saveImages',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);


$router->post(
    '/api/admin/categories/page/images/mobile',
    'CategoryController@saveMobileImage',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);


$router->post(
    '/api/admin/categories/page/images/favorite',
    'CategoryController@favoriteImage',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/page/images/delete',
    'CategoryController@deleteImage',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/categories/page/images/order',
    'CategoryController@updateImageOrder',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/admin/pages',
    'PageController@index',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/admin/pages/create',
    'PageController@create',
    [
        ['Middleware', 'webRole', ['superadmin']]
    ]
);

$router->get(
    '/admin/pages/edit',
    'PageController@edit',
    [
        ['Middleware', 'webRole', ['superadmin']]
    ]
);

$router->get(
    '/api/admin/pages',
    'PageController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/pages/save',
    'PageController@save',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin']]
    ]
);

$router->post(
    '/api/admin/pages/toggle',
    'PageController@toggle',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin']]
    ]
);

$router->get(
    '/admin/events',
    'EventController@index',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin', 'staff']]
    ]
);

$router->get(
    '/api/admin/events',
    'EventController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

$router->post(
    '/api/admin/events/status',
    'EventController@status',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

$router->get(
    '/admin/profile',
    'UserController@profile',
    [
        ['Middleware', 'webAuth', []]
    ]
);

$router->post(
    '/api/admin/email/test',
    'EmailController@test',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin']]
    ]
);

$router->get(
    '/api/admin/notifications',
    'NotificationController@index',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

$router->get(
    '/api/admin/notifications/unread-count',
    'NotificationController@unreadCount',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

$router->post(
    '/api/admin/notifications/read',
    'NotificationController@read',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

$router->post(
    '/api/admin/notifications/read-all',
    'NotificationController@readAll',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/settings',
    'SettingsController@index',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/settings/update',
    'SettingsController@update',
    [
        [
            'Middleware',
            'role',
            ['superadmin']
        ]
    ]
);

/*
|--------------------------------------------------------------------------
| USERS
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/users',
    'UserController@index',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);

$router->get(
    '/admin/users/create',
    'UserController@create',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);

$router->get(
    '/admin/users/edit',
    'UserController@edit',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);

$router->get(
    '/api/admin/users',
    'UserController@data',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->get(
    '/api/admin/users/meta',
    'UserController@meta',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->get(
    '/api/admin/users/permissions',
    'UserController@permissions',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/admin/users/store',
    'UserController@store',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/admin/users/update',
    'UserController@update',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/admin/users/delete',
    'UserController@delete',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/admin/users/permissions',
    'UserController@savePermissions',
    [
        [
            'Middleware',
            'auth',
            []
        ],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);

/*
|--------------------------------------------------------------------------
| BRANCHES / SEDES
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/branches',
    'BranchController@index',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);


$router->get(
    '/admin/branches/create',
    'BranchController@create',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin']
        ]
    ]
);


$router->get(
    '/admin/branches/edit',
    'BranchController@edit',
    [
        [
            'Middleware',
            'webRole',
            ['superadmin', 'admin']
        ]
    ]
);


$router->get(
    '/api/admin/branches',
    'BranchController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);


$router->get(
    '/api/admin/branches/show',
    'BranchController@show',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);


$router->post(
    '/api/admin/branches/store',
    'BranchController@store',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin']]
    ]
);


$router->post(
    '/api/admin/branches/update',
    'BranchController@update',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/branches/images/save',
    'BranchController@saveImages',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']
        ]
    ]
);

$router->post(
    '/api/admin/branches/save-hours',
    'BranchController@saveHours',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/api/admin/branches/tables',
    'BranchController@tables',
    [
        ['Middleware', 'auth', []],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);


$router->post(
    '/api/admin/branches/tables/create',
    'BranchController@createTable',
    [
        ['Middleware', 'auth', []],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);


$router->post(
    '/api/admin/branches/tables/update',
    'BranchController@updateTable',
    [
        ['Middleware', 'auth', []],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);


$router->post(
    '/api/admin/branches/tables/delete',
    'BranchController@deleteTable',
    [
        ['Middleware', 'auth', []],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);


$router->post(
    '/api/admin/branches/tables/delete-permanently',
    'BranchController@deleteTablePermanently',
    [
        ['Middleware', 'auth', []],
        [
            'Middleware',
            'role',
            ['superadmin', 'admin']
        ]
    ]
);


$router->post(
    '/api/admin/branches/delete',
    'BranchController@delete',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin']]
    ]
);

/*
|--------------------------------------------------------------------------
| ITEMS
|--------------------------------------------------------------------------
*/

$router->get(
    '/api/items',
    'ItemController@index'
);

$router->get(
    '/api/items/show',
    'ItemController@show'
);

$router->post(
    '/api/items/store',
    'ItemController@store',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/items/update',
    'ItemController@update',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/items/delete',
    'ItemController@delete',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

/*
|--------------------------------------------------------------------------
| ADMIN ITEMS / PRODUCTOS
|--------------------------------------------------------------------------
*/

/*
 * Página principal: requiere permiso de lectura.
 */
$router->get(
    '/admin/items',
    'ItemController@adminIndex',
    [
        ['Middleware', 'webAnyPermission', ['products.read']]
    ]
);

/* Crear producto */
$router->get(
    '/admin/items/create',
    'ItemController@create',
    [
        ['Middleware', 'webAnyPermission', ['products.create']]
    ]
);

/* Editar producto */
$router->get(
    '/admin/items/edit',
    'ItemController@edit',
    [
        ['Middleware', 'webAnyPermission', ['products.update']]
    ]
);

/* Lista de productos */
$router->get(
    '/api/admin/items',
    'ItemController@data',
    [
        ['Middleware', 'anyPermission', ['products.read']]
    ]
);

/* Producto individual */
$router->get(
    '/api/admin/items/show',
    'ItemController@adminShow',
    [
        ['Middleware', 'anyPermission', ['products.read']]
    ]
);

/* Categorías necesarias para crear o editar productos */
$router->get(
    '/api/admin/items/categories',
    'ItemController@categories',
    [
        ['Middleware', 'anyPermission', ['products.create', 'products.update', 'products.read']]
    ]
);

/* Reglas dinámicas de categoría */
$router->get(
    '/api/admin/items/category-rules',
    'ItemController@categoryRules',
    [
        ['Middleware', 'anyPermission', ['products.create', 'products.update']]
    ]
);

/* Crear */
$router->post(
    '/api/admin/items/store',
    'ItemController@store',
    [
        ['Middleware', 'anyPermission', ['products.create']]
    ]
);

/* Guardar imágenes al crear o actualizar */
$router->post(
    '/api/admin/items/images/save',
    'ItemController@saveImages',
    [
        ['Middleware', 'anyPermission', ['products.create', 'products.update']]
    ]
);

/* Actualizar */
$router->post(
    '/api/admin/items/update',
    'ItemController@update',
    [
        ['Middleware', 'anyPermission', ['products.update']]
    ]
);

/* Eliminar / desactivar */
$router->post(
    '/api/admin/items/delete',
    'ItemController@delete',
    [
        ['Middleware', 'anyPermission', ['products.delete']]
    ]
);

/* Activar producto: se considera una actualización */
$router->post(
    '/api/admin/items/activate',
    'ItemController@activate',
    [
        ['Middleware', 'anyPermission', ['products.update']]
    ]
);


/*
|--------------------------------------------------------------------------
| CARRITO Y CHECKOUT PÚBLICO
|--------------------------------------------------------------------------
*/

$router->get(
    '/carrito',
    'CartController@index'
);

$router->get(
    '/checkout',
    'CartController@checkout',
    [
        ['Middleware', 'webAuth', []]
    ]
);

$router->get(
    '/checkout/resultado',
    'CartController@paymentResult',
    [
        ['Middleware', 'webAuth', []]
    ]
);


/*
|--------------------------------------------------------------------------
| ADMIN - CUPONES
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/coupons',
    'CouponController@index',
    [
        ['Middleware', 'webRole', ['superadmin', 'admin']]
    ]
);

$router->get(
    '/api/admin/coupons',
    'CouponController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/coupons/store',
    'CouponController@store',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/coupons/update',
    'CouponController@update',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/admin/coupons/toggle',
    'CouponController@toggle',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin']]
    ]
);

$router->post(
    '/api/coupons/validate',
    'CouponController@validatePublic',
    [
        ['Middleware', 'auth', []]
    ]
);


/*
|--------------------------------------------------------------------------
| ORDERS
|--------------------------------------------------------------------------
*/

$router->post(
    '/api/orders/create',
    'OrderController@create',
    [
        ['Middleware', 'auth', []]
    ]
);

$router->get(
    '/api/orders/show',
    'OrderController@show',
    [
        ['Middleware', 'auth', []]
    ]
);


/*
|--------------------------------------------------------------------------
| PAYMENTS
|--------------------------------------------------------------------------
*/

$router->post(
    '/api/payments/preflight',
    'PaymentController@preflight',
    [
        ['Middleware', 'auth', []]
    ]
);

$router->post(
    '/api/payments/checkout',
    'PaymentController@checkout',
    [
        ['Middleware', 'auth', []]
    ]
);

$router->post(
    '/api/payments/webhook',
    'PaymentController@webhook'
);

$router->get(
    '/api/payments/status',
    'PaymentController@status',
    [
        ['Middleware', 'auth', []]
    ]
);



/*
|--------------------------------------------------------------------------
| RESTAURANT RESERVATIONS
|--------------------------------------------------------------------------
|
| Página pública:
|
| GET /reservas
|
| API pública:
|
| GET  /api/public/branches
| GET  /api/public/availability
| POST /api/public/reservations
|
| La lógica permanece en:
|
| Controller
|     ↓
| Services
|     ↓
| Models
|     ↓
| Database
|
|--------------------------------------------------------------------------
*/

$router->get(
    '/reservas',
    'RestaurantReservationController@page'
);

$router->get(
    '/api/public/branches',
    'RestaurantReservationController@branches'
);

$router->get(
    '/api/public/availability',
    'RestaurantReservationController@availability'
);

$router->post(
    '/api/public/reservations',
    'RestaurantReservationController@create'
);

$router->post(
    '/api/public/reservations/cancel',
    'RestaurantReservationController@cancel'
);

$router->get(
    '/api/public/branches/hours',
    'BranchController@publicHours'
);

/*
|--------------------------------------------------------------------------
| ADMIN RESERVATIONS
|--------------------------------------------------------------------------
*/

$router->get(
    '/api/admin/reservations',
    'AdminReservationController@data',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

/*
|--------------------------------------------------------------------------
| METRICS
|--------------------------------------------------------------------------
*/

$router->get(
    '/api/metrics/sales',
    'ApiController@salesMetrics',
    [
        ['Middleware', 'auth', []],
        ['Middleware', 'role', ['superadmin', 'admin', 'staff']]
    ]
);

/*
|--------------------------------------------------------------------------
| RESTAURANT RESERVATIONS
|--------------------------------------------------------------------------
*/


$router->get(
    '/consultar-reserva',
    'RestaurantReservationController@searchPage'
);

$router->get(
    '/api/public/reservations/search',
    'RestaurantReservationController@search'
);


$router->post(
    '/api/newsletter/subscribe',
    'NewsletterController@subscribe'
);



/*
|--------------------------------------------------------------------------
| DISPATCH
|--------------------------------------------------------------------------
*/

$router->dispatch();