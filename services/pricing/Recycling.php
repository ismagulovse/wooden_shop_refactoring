<?php
namespace Wooden;

final class WeightRecycling
{
    private ?int $billableKg = null;

    public function fee(int $weightKg): int
    {
        $this->billableKg = max(0, $weightKg - 20);
        try {
            return 50000 + $this->disposalCharge();
        } finally {
            $this->billableKg = null;
        }
    }

    private function disposalCharge(): int
    {
        // Возвращаем результат.
        return $this->billableKg * 1500;
    }
}

final class ItemRecycling
{
    public function removalCost(int $pieces): array
    {
        return ['amount_kopecks' => 30000 + $pieces * 40000];
    }
}
