<?php
namespace Wooden;

final class ExtendedWarranty
{
    private ?int $coveredValue = null;

    public function premium(int $subtotalKopecks, int $months): int
    {
        $this->coveredValue = $subtotalKopecks;
        try {
            // Любая часть года оплачивается как полный год дополнительной гарантии.
            return $this->annualPremium() * (int) ceil($months / 12);
        } finally {
            $this->coveredValue = null;
        }
    }

    private function annualPremium(): int
    {
        return intdiv($this->coveredValue * 2, 100);
    }
}

final class FixedWarranty
{
    public function planCost(array $contract): array
    {
        return ['rubles' => $contract['units'] * 500 * (int) ceil($contract['months'] / 12)];
    }
}
