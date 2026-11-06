<?php $flashMain = flash('main'); ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Wooden Admin') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-aside">
        <div class="admin-brand-wrap">
            <a href="/admin" class="admin-brand">Wooden Admin</a>
            <p>Панель управления магазином</p>
        </div>

        <nav class="admin-nav">
            <a href="/admin">Dashboard</a>
            <a href="/admin/products">Товары</a>
            <a href="/admin/categories">Категории</a>
            <a href="/admin/orders">Заказы</a>
            <a href="/admin/users">Пользователи</a>
            <a href="/">На сайт</a>
        </nav>
    </aside>

    <section class="admin-content">
        <?php if ($flashMain): ?>
            <div class="flash flash-<?= e($flashMain['type']) ?>"><?= e($flashMain['message']) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </section>
</div>
</body>
</html>
