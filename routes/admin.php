<?php

$router->get('/', [AdminDashboardController::class, 'index']);

$router->get('/categories', [AdminCategoryController::class, 'index']);
$router->get('/categories/create', [AdminCategoryController::class, 'create']);
$router->post('/categories/store', [AdminCategoryController::class, 'store']);
$router->get('/categories/{id}/edit', [AdminCategoryController::class, 'edit']);
$router->post('/categories/{id}/update', [AdminCategoryController::class, 'update']);
$router->post('/categories/{id}/delete', [AdminCategoryController::class, 'delete']);
$router->post('/categories/{id}/restore', [AdminCategoryController::class, 'restore']);

$router->get('/products', [AdminProductController::class, 'index']);
$router->get('/products/create', [AdminProductController::class, 'create']);
$router->post('/products/store', [AdminProductController::class, 'store']);
$router->get('/products/{id}/edit', [AdminProductController::class, 'edit']);
$router->post('/products/{id}/update', [AdminProductController::class, 'update']);
$router->post('/products/{id}/delete', [AdminProductController::class, 'delete']);
$router->post('/products/{id}/restore', [AdminProductController::class, 'restore']);

$router->get('/orders', [AdminOrderController::class, 'index']);
$router->get('/orders/{id}', [AdminOrderController::class, 'show']);
$router->post('/orders/{id}/status', [AdminOrderController::class, 'status']);
$router->post('/orders/{id}/delete', [AdminOrderController::class, 'delete']);
$router->post('/orders/{id}/restore', [AdminOrderController::class, 'restore']);

$router->get('/users', [AdminUserController::class, 'index']);
$router->get('/users/{id}', [AdminUserController::class, 'show']);
$router->post('/users/{id}/status', [AdminUserController::class, 'status']);
$router->post('/users/{id}/delete', [AdminUserController::class, 'delete']);
$router->post('/users/{id}/restore', [AdminUserController::class, 'restore']);

