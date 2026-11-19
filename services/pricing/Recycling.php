<?php
namespace Wooden;

final class WeightRecycling
{
    private const INCLUDED_KG = 20;

    private ?int $billableKg = null;

    public function fee(int $weightKg): int
    {
        $this->billableKg = max(0, $weightKg - self::INCLUDED_KG);
        try {
            return 50000 + $this->disposalCharge();
        } finally {
            $this->billableKg = null;
        }
    }

    private function disposalCharge(): int
    {
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
