<?php require ROOT_PATH . '/views/partials/breadcrumbs.php'; ?>
<section class="checkout-grid">
    <div>
        <h1>Оформление заказа</h1>
        <form action="/checkout/place" method="post" class="form" data-validate>
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="quote_fingerprint" value="<?= e(hash('sha256', json_encode($quote, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) ?>">
            <label>Имя получателя<input name="customer_name" required value="<?= e($form['customer_name'] ?? ($user['first_name'] . ' ' . $user['last_name'])) ?>"></label>
            <label>Телефон<input name="customer_phone" required value="<?= e($form['customer_phone'] ?? $user['phone']) ?>"></label>
            <label>Email<input type="email" name="customer_email" required value="<?= e($form['customer_email'] ?? $user['email']) ?>"></label>
            <label>Адрес доставки<textarea name="delivery_address" required><?= e($form['delivery_address'] ?? '') ?></textarea></label>
            <label>Комментарий<textarea name="comment"><?= e($form['comment'] ?? '') ?></textarea></label>
            <label>Способ оплаты
                <select name="payment_method" required>
                    <option value="card" <?= ($form['payment_method'] ?? 'card') === 'card' ? 'selected' : '' ?>>Карта</option>
                    <option value="cash" <?= ($form['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Наличными</option>
                </select>
            </label>
            <h2>Доставка и услуги</h2>
            <?php foreach (['delivery_method' => 'Доставка', 'packaging' => 'Упаковка', 'assembly' => 'Сборка', 'warranty' => 'Дополнительная гарантия', 'recycling' => 'Вывоз старой мебели'] as $key => $label): ?>
                <label><?= e($label) ?>
                    <select name="<?= e($key) ?>">
                        <?php foreach (\Wooden\OrderPricing::OPTIONS[$key] as $value => $text): ?>
                            <option value="<?= e($value) ?>" <?= $quote['options'][$key] === $value ? 'selected' : '' ?>><?= e($text) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
            <details>
                <summary>Параметры выбранных услуг</summary>
                <div class="form">
                    <?php foreach (['distance' => 'Расстояние от магазина, км (курьер и сборка на месте)', 'weight' => 'Общий вес новой мебели, кг (грузовая доставка)', 'months' => 'Срок дополнительной гарантии, месяцев', 'old_weight' => 'Вес старой мебели, кг (вывоз по весу)', 'old_units' => 'Число старых предметов (вывоз по количеству)'] as $key => $label): ?>
                        <label><?= e($label) ?><input type="number" name="<?= e($key) ?>" min="<?= (int)\Wooden\OrderPricing::LIMITS[$key][0] ?>" max="<?= (int)\Wooden\OrderPricing::LIMITS[$key][1] ?>" step="1" value="<?= (int)$quote['options'][$key] ?>" required></label>
                    <?php endforeach; ?>
                </div>
            </details>
            <p>Укажите параметры выбранных услуг перед расчётом. Из двух скидок автоматически применяется большая: 3% от суммы покупки (7% от 100 000 ₽) или 5% за комплект от трёх предметов. Скидки не суммируются.</p>
            <button class="btn btn-light" type="submit" formaction="/checkout/preview" formnovalidate>Пересчитать чек</button>
            <button class="btn" type="submit">Подтвердить заказ</button>
        </form>
    </div>

    <article class="receipt-card checkout-receipt-preview">
        <header class="receipt-head">
            <div>
                <p class="receipt-kicker">Предварительный чек</p>
                <h2>Ваш заказ</h2>
                <p class="receipt-meta">Финальный чек будет сформирован после подтверждения</p>
            </div>
            <div class="receipt-dates">
                <div>
                    <span>Время заказа</span>
                    <strong><?= e($previewOrderDateText) ?></strong>
                </div>
                <div>
                    <span>Ожидаемая доставка</span>
                    <strong><?= e($previewDeliveryDateText) ?></strong>
                </div>
            </div>
        </header>

        <section class="receipt-info-grid">
            <div class="receipt-block">
                <h3>Заказчик</h3>
                <p><strong><?= e($form['customer_name'] ?? ($user['first_name'] . ' ' . $user['last_name'])) ?></strong></p>
                <p><?= e($form['customer_phone'] ?? $user['phone']) ?></p>
                <p><?= e($form['customer_email'] ?? $user['email']) ?></p>
            </div>
            <div class="receipt-block">
                <h3>Поставщик</h3>
                <p><strong><?= e($supplier['name']) ?></strong></p>
                <p><?= e($supplier['contact']) ?></p>
                <p><?= e($supplier['email']) ?></p>
                <p><?= e($supplier['address']) ?></p>
            </div>
            <div class="receipt-block">
                <h3>Позиции заказа</h3>
                <?php foreach ($items as $item): ?>
                    <p><?= e($item['name']) ?> x <?= (int)$item['quantity'] ?> - <strong><?= money_format_local((float)$item['price'] * (int)$item['quantity']) ?></strong></p>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="receipt-block">
            <h3>Услуги и скидка</h3>
            <p>Товары: <?= e(number_format($quote['subtotal'] / 100, 2, ',', ' ')) ?> ₽</p>
            <?php foreach ($quote['lines'] as $line): ?>
                <p><?= e($line['label']) ?>: <strong><?= e(number_format($line['amount'] / 100, 2, ',', ' ')) ?> ₽</strong></p>
            <?php endforeach; ?>
            <p>После изменения услуг нажмите «Пересчитать чек».</p>
        </section>
        <footer class="receipt-total-row">
            <div>
                <span>Итого</span>
                <strong><?= e(number_format((float)$total, 2, ',', ' ')) ?> ₽</strong>
            </div>
        </footer>
    </article>
</section>
