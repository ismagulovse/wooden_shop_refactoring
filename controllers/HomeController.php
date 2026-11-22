<?php

class HomeController extends BaseController
{
    public function index(): void
    {
        $productModel = new Product();
        $categoryModel = new Category();

        $favoriteIds = [];
        if (is_logged_in()) {
            $favoriteIds = (new Favorite())->productIdsByUser((int) user_id());
        }

        $this->render('home/index', [
            'title' => 'Wooden - интернет-магазин деревянной мебели',
            'featured' => $productModel->featured(6),
            'categories' => $categoryModel->active(),
            'favoriteIds' => $favoriteIds,
        ]);
    }
}