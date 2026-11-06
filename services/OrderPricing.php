<?php
namespace Wooden;

foreach (['Delivery', 'Packaging', 'Assembly', 'Discount', 'Warranty', 'Recycling'] as $file) {
    require_once __DIR__ . '/pricing/' . $file . '.php';
}

final class OrderPricing
{
    public const OPTIONS = [
        'delivery_method' => ['pickup' => 'Самовывоз — бесплатно', 'courier' => 'Курьер', 'freight' => 'Грузовой перевозчик'],
        'packaging' => ['none' => 'Без дополнительной упаковки', 'protective' => 'Защитная упаковка', 'crate' => 'Деревянные короба'],
        'assembly' => ['none' => 'Без сборки', 'workshop' => 'Сборка в мастерской', 'onsite' => 'Сборка на месте'],
        'warranty' => ['none' => 'Без дополнительной гарантии', 'extended' => 'Гарантия от стоимости мебели', 'fixed' => 'Фиксированная гарантия'],
        'recycling' => ['none' => 'Без вывоза', 'weight' => 'Вывоз по весу', 'items' => 'Вывоз по количеству'],
    ];
    public const DEFAULTS = ['delivery_method' => 'pickup', 'packaging' => 'none', 'assembly' => 'none', 'warranty' => 'none', 'recycling' => 'none', 'distance' => 0, 'weight' => 1, 'months' => 12, 'old_weight' => 1, 'old_units' => 1];
    public const LIMITS = ['distance' => [0, 1000], 'weight' => [1, 5000], 'months' => [1, 60], 'old_weight' => [1, 5000], 'old_units' => [1, 100]];

    public function options(array $input): array
    {
        $options = self::DEFAULTS;
        foreach (self::OPTIONS as $key => $allowed) {
            $value = $input[$key] ?? $options[$key];
            if (!is_string($value) || !isset($allowed[$value])) {
                throw new \DomainException('Выберите допустимый вариант услуги.');
            }
            $options[$key] = $value;
        }
        foreach (self::LIMITS as $key => [$min, $max]) {
            $raw = $input[$key] ?? $options[$key];
            $value = is_scalar($raw) ? filter_var($raw, FILTER_VALIDATE_INT) : false;
            if ($value === false || $value < $min || $value > $max) {
                throw new \DomainException('Проверьте расстояние, вес, количество и срок гарантии.');
            }
            $options[$key] = $value;
        }
        return $options;
    }

    public function calculate(array $items, array $input): array
    {
        $o = $this->options($input);
        $subtotal = 0;
        $units = 0;
        foreach ($items as $item) {
            $quantity = (int)$item['quantity'];
            if ($quantity < 1 || (float)$item['price'] < 0) {
                throw new \DomainException('Некорректная позиция корзины.');
            }
            $subtotal += (int)round((float)$item['price'] * 100) * $quantity;
            $units += $quantity;
        }
        if ($units === 0) {
            throw new \DomainException('Корзина пуста.');
        }
        $lines = [];
        $add = static function (string $code, string $label, int $amount) use (&$lines): void {
            $lines[] = ['code' => $code, 'label' => $label, 'amount' => $amount];
        };
        if ($o['delivery_method'] === 'courier') {
            $add('delivery', 'Курьер: ' . $o['distance'] . ' км', (new CourierDelivery())->quote($o['distance']));
        } elseif ($o['delivery_method'] === 'freight') {
            $quote = (new FreightDelivery())->estimate(['weight_kg' => $o['weight']]);
            $add('delivery', 'Грузовая доставка: ' . $o['weight'] . ' кг', $quote['rubles'] * 100);
        } else {
            $add('delivery', 'Самовывоз', 0);
        }
        if ($o['packaging'] === 'protective') {
            $add('packaging', 'Защитная упаковка: ' . $units . ' шт.', (new ProtectivePackaging())->price($units));
        } elseif ($o['packaging'] === 'crate') {
            $quote = (new CratePackaging())->calculateCrates(['units' => $units]);
            $add('packaging', 'Деревянные короба: ' . $units . ' предметов', $quote['amount_kopecks']);
        }
        if ($o['assembly'] === 'workshop') {
            $add('assembly', 'Сборка в мастерской: ' . $units . ' шт.', (new WorkshopAssembly())->calculate($units));
        } elseif ($o['assembly'] === 'onsite') {
            $quote = (new OnSiteAssembly())->visitPrice($units, $o['distance']);
            $add('assembly', 'Выездная сборка: ' . $units . ' шт., ' . $o['distance'] . ' км', $quote['rubles'] * 100);
        }
        // Скидки не суммируются: покупателю автоматически применяется большая.
        $loyalty = (new LoyaltyDiscount())->discount($subtotal);
        $bundle = (new BundleDiscount())->apply(['units' => $units, 'subtotal_kopecks' => $subtotal]);
        $saving = $subtotal - $bundle['payable_kopecks'];
        $add('discount', $saving > $loyalty ? 'Скидка за комплект' : 'Скидка на сумму покупки', -max($loyalty, $saving));
        if ($o['warranty'] === 'extended') {
            $add('warranty', 'Гарантия от стоимости: ' . $o['months'] . ' мес.', (new ExtendedWarranty())->premium($subtotal, $o['months']));
        } elseif ($o['warranty'] === 'fixed') {
            $quote = (new FixedWarranty())->planCost(['units' => $units, 'months' => $o['months']]);
            $add('warranty', 'Фиксированная гарантия: ' . $o['months'] . ' мес.', $quote['rubles'] * 100);
        }
        if ($o['recycling'] === 'weight') {
            $add('recycling', 'Вывоз старой мебели: ' . $o['old_weight'] . ' кг', (new WeightRecycling())->fee($o['old_weight']));
        } elseif ($o['recycling'] === 'items') {
            $quote = (new ItemRecycling())->removalCost($o['old_units']);
            $add('recycling', 'Вывоз старой мебели: ' . $o['old_units'] . ' шт.', $quote['amount_kopecks']);
        }
        return ['options' => $o, 'lines' => $lines, 'subtotal' => $subtotal, 'total' => $subtotal + array_sum(array_column($lines, 'amount'))];
    }
}
