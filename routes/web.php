<?php

$router->get('/', [HomeController::class, 'index']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/catalog', [CatalogController::class, 'index']);
$router->get('/catalog/{slug}', [CatalogController::class, 'show']);

$router->get('/profile', [ProfileController::class, 'index']);
$router->post('/profile/update', [ProfileController::class, 'updateProfile']);
$router->post('/profile/password', [ProfileController::class, 'updatePassword']);
$router->post('/profile/orders/{id}/cancel', [ProfileController::class, 'cancelOrder']);

$router->get('/favorites', [FavoriteController::class, 'index']);
$router->post('/favorites/add', [FavoriteController::class, 'add']);
$router->post('/favorites/remove', [FavoriteController::class, 'remove']);
$router->post('/favorites/move-to-cart', [FavoriteController::class, 'moveToCart']);

$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/clear', [CartController::class, 'clear']);

$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout/preview', [CheckoutController::class, 'index']);
$router->post('/checkout/place', [CheckoutController::class, 'place']);
$router->get('/checkout/success/{id}', [CheckoutController::class, 'success']);

