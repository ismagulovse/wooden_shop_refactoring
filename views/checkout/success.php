<section class="receipt-wrap">
    <article class="receipt-card" id="receiptToImage">
        <header class="receipt-head">
            <div>
                <p class="receipt-kicker">Электронный чек</p>
                <h1>Заказ #<?= (int)$order['id'] ?></h1>
                <p class="receipt-meta">Статус: <?= e(translate_order_status($order['status'])) ?></p>
            </div>
            <?php require ROOT_PATH . '/views/partials/receipt-dates.php'; ?>
        </header>

        <section class="receipt-info-grid">
            <div class="receipt-block">
                <?php require ROOT_PATH . '/views/partials/receipt-customer.php'; ?>
                <p><?= e($order['delivery_address']) ?></p>
                <?php if (!empty($order['comment'])): ?>
                    <p class="muted">Комментарий: <?= e($order['comment']) ?></p>
                <?php endif; ?>
            </div>

            <?php require ROOT_PATH . '/views/partials/receipt-supplier.php'; ?>

            <div class="receipt-block">
                <h3>Оплата и доставка</h3>
                <p>Способ оплаты: <strong><?= e(translate_payment_method($order['payment_method'])) ?></strong></p>
                <p>Способ доставки: <strong><?= e(translate_delivery_method($order['delivery_method'])) ?></strong></p>
                <p class="muted">Вы можете отменить заказ в течение 15 минут после оформления.</p>
            </div>
        </section>

        <div class="table-wrap receipt-table-wrap">
            <table class="table receipt-table">
                <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
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

        <?php require ROOT_PATH . '/views/partials/order-charges.php'; ?>
        <footer class="receipt-total-row">
            <div>
                <span>Итого к оплате</span>
                <strong><?= e(number_format((float)$order['total_amount'], 2, ',', ' ')) ?> ₽</strong>
            </div>
            <div class="card-actions">
                <button type="button" id="downloadReceiptBtn" class="btn btn-light">Скачать чек (PNG)</button>
                <a href="/profile" class="btn">Перейти в личный кабинет</a>
            </div>
        </footer>
    </article>
</section>
