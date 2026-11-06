<section class="admin-page admin-form-page">
    <div class="admin-head-row">
        <h1 class="admin-title">Заказ #<?= (int)$order['id'] ?></h1>
        <a href="/admin/orders" class="admin-btn admin-btn-secondary">К заказам</a>
    </div>

    <div class="admin-stack">
        <div class="admin-info-card">
            <h2>Информация о заказе</h2>

            <div class="admin-order-info-vertical">
                <div><strong>Клиент:</strong> <?= e($order['customer_name']) ?></div>
                <div><strong>Телефон:</strong> <?= e($order['customer_phone']) ?></div>
                <div><strong>Email:</strong> <?= e($order['customer_email']) ?></div>
                <div><strong>Адрес:</strong> <?= e($order['delivery_address']) ?></div>
                <div><strong>Оплата:</strong> <?= e(translate_payment_method($order['payment_method'])) ?></div>
                <div><strong>Доставка:</strong> <?= e(translate_delivery_method($order['delivery_method'])) ?></div>
                <div><strong>Комментарий:</strong> <?= e($order['comment'] ?: '—') ?></div>
                <div><strong>Создан:</strong> <?= e($order['created_at']) ?></div>
                <div><strong>Сумма:</strong> <?= money_format_local((float)$order['total_amount']) ?></div>
            </div>
        </div>

        <div class="admin-info-card">
            <h2>Статус заказа</h2>
            <form action="/admin/orders/<?= (int)$order['id'] ?>/status" method="post" class="form admin-form-inline">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

                <label>Текущий статус
                    <select name="status">
                        <?php foreach (Order::STATUSES as $status): ?>
                            <option value="<?= e($status) ?>" <?= $status === $order['status'] ? 'selected' : '' ?>>
                                <?= e(translate_order_status($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button class="admin-btn admin-btn-primary" type="submit">Сохранить</button>
            </form>
        </div>

        <div class="admin-info-card">
            <h2>Состав заказа</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Кол-во</th>
                        <th>Итого</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= e($item['product_name']) ?></td>
                            <td><?= money_format_local((float)$item['price']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td><?= money_format_local((float)$item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php require ROOT_PATH . '/views/partials/order-charges.php'; ?>
        <div class="admin-actions-row">
            <?php if (!$order['deleted_at']): ?>
                <form action="/admin/orders/<?= (int)$order['id'] ?>/delete" method="post" class="inline-form">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <button class="admin-btn admin-btn-danger" type="submit">Удалить</button>
                </form>
            <?php else: ?>
                <form action="/admin/orders/<?= (int)$order['id'] ?>/restore" method="post" class="inline-form">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <button class="admin-btn admin-btn-success" type="submit">Восстановить</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
