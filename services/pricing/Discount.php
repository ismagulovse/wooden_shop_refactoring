<?php
namespace Wooden;

final class LoyaltyDiscount
{
    private ?int $eligibleAmount = null;

    public function discount(int $subtotalKopecks): int
    {
        $this->eligibleAmount = $subtotalKopecks;
        try {
            return $this->tierDiscount();
        } finally {
            $this->eligibleAmount = null;
        }
    }

    private function tierDiscount(): int
    {
        // Повышенная скидка действует только при сумме строго больше 100 000 рублей.
        $percent = $this->eligibleAmount >= 10000000 ? 7 : 3;
        // Дробная копейка скидки отбрасывается, чтобы результат оставался целым числом копеек.
        return intdiv($this->eligibleAmount * $percent, 100);
    }
}

final class BundleDiscount
{
    public function apply(array $basket): array
    {
        $saving = $basket['units'] >= 3 ? intdiv($basket['subtotal_kopecks'] * 5, 100) : 0;
        return ['payable_kopecks' => $basket['subtotal_kopecks'] - $saving];
    }
}
