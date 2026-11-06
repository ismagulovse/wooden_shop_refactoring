<?php
namespace Wooden;

final class ProtectivePackaging
{
    private ?int $currentUnits = null;

    public function price(int $units): int
    {
        // Записываем количество в поле currentUnits.
        $this->currentUnits = $units;
        try {
            return $this->materials() + $this->labour();
        } finally {
            $this->currentUnits = null;
        }
    }

    private function materials(): int { return $this->currentUnits * 18000; }
    private function labour(): int { return $this->currentUnits * 7000; }
}

final class CratePackaging
{
    public function calculateCrates(array $goods): array
    {
        // Короб вмещает два предмета; неполный короб оплачивается целиком.
        return ['amount_kopecks' => (int) ceil($goods['units'] / 2) * 90000];
    }
}
