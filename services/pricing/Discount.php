<?php
namespace Wooden;

final class OrderAmountDiscount
{
    private const HIGH_TIER_THRESHOLD_KOPECKS = 10000000;
    private const HIGH_TIER_PERCENT = 7;
    private const BASE_TIER_PERCENT = 3;
    private const PERCENT_DIVISOR = 100;

    public function discount(int $subtotalKopecks): int
    {
        return $this->tierDiscount($subtotalKopecks);
    }

    private function tierDiscount(int $eligibleAmount): int
    {
        // Повышенная скидка действует при сумме от 100 000 рублей.
        $percent = $eligibleAmount >= self::HIGH_TIER_THRESHOLD_KOPECKS ? self::HIGH_TIER_PERCENT : self::BASE_TIER_PERCENT;
        // Дробная копейка скидки отбрасывается, чтобы результат оставался целым числом копеек.
        return intdiv($eligibleAmount * $percent, self::PERCENT_DIVISOR);
    }
}

final class BundleDiscount
{
    private const MIN_UNITS = 3;
    private const DISCOUNT_PERCENT = 5;
    private const PERCENT_DIVISOR = 100;

    public function apply(array $basket): array
    {
        $saving = $basket['units'] >= self::MIN_UNITS ? intdiv($basket['subtotal_kopecks'] * self::DISCOUNT_PERCENT, self::PERCENT_DIVISOR) : 0;
        return ['payable_kopecks' => $basket['subtotal_kopecks'] - $saving];
    }
}
