<?php

class CatalogController extends BaseController
{
    public function index(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? ''),
            'price_min' => trim($_GET['price_min'] ?? ''),
            'price_max' => trim($_GET['price_max'] ?? ''),
            'material' => trim($_GET['material'] ?? ''),
            'color' => trim($_GET['color'] ?? ''),
            'in_stock' => trim($_GET['in_stock'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
        ];

        $sort = $_GET['sort'] ?? 'date_desc';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $productModel = new Product();
        $categoryModel = new Category();
        $result = $productModel->catalog($filters, $sort, $page, ITEMS_PER_PAGE);
        $pagination = paginate($result['total'], $page, ITEMS_PER_PAGE);

        $favoriteIds = [];
        if (is_logged_in()) {
            $favoriteIds = (new Favorite())->productIdsByUser((int) user_id());
        }

        $this->render('catalog/index', [
            'title' => 'Каталог',
            'products' => $result['items'],
            'filters' => $filters,
            'sort' => $sort,
            'categories' => $categoryModel->active(),
            'pagination' => $pagination,
            'favoriteIds' => $favoriteIds,
            'breadcrumbs' => [
                ['title' => 'Главная', 'url' => '/'],
                ['title' => 'Каталог', 'url' => '/catalog'],
            ],
        ]);
    }

    public function show(string $slug): void
    {
        $productModel = new Product();
        $product = $productModel->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $similar = $productModel->similar((int) $product['category_id'], (int) $product['id']);

        $this->render('catalog/show', [
            'title' => $product['name'],
            'product' => $product,
            'similar' => $similar,
            'breadcrumbs' => [
                ['title' => 'Главная', 'url' => '/'],
                ['title' => 'Каталог', 'url' => '/catalog'],
                ['title' => $product['name'], 'url' => ''],
            ],
        ]);
    }
}