<?php
namespace Wooden;

final class WorkshopAssembly
{
    private ?int $minutesForOrder = null;

    public function calculate(int $units): int
    {
        // На сборку каждого предмета мебели закладывается 45 минут.
        $this->minutesForOrder = $units * 45;
        try {
            return $this->workCharge();
        } finally {
            $this->minutesForOrder = null;
        }
    }

    private function workCharge(): int
    {
        return max(60000, $this->minutesForOrder * 2000);
    }
}

final class OnSiteAssembly
{
    public function visitPrice(int $itemCount, int $distanceKm): array
    {
        return ['rubles' => 1000 + $itemCount * 700 + max(0, $distanceKm - 10) * 20];
    }
}
