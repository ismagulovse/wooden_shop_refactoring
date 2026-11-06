<?php if (!empty($order['charges'])): ?>
    <section class="receipt-block">
        <h3>Услуги и скидка</h3>
        <p>Товары: <?= e(number_format(array_sum(array_column($order['items'], 'total')), 2, ',', ' ')) ?> ₽</p>
        <?php foreach ($order['charges'] as $charge): ?>
            <p><?= e($charge['label']) ?>: <strong><?= e(number_format((float)$charge['amount'], 2, ',', ' ')) ?> ₽</strong></p>
        <?php endforeach; ?>
        <p>К оплате: <strong><?= e(number_format((float)$order['total_amount'], 2, ',', ' ')) ?> ₽</strong></p>
    </section>
<?php endif; ?>
