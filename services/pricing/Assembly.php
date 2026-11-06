<?php
namespace Wooden;

final class WorkshopAssembly
{
    private ?int $minutesForOrder = null;

    public function calculate(int $units): int
    {
        // Здесь необходимо взять количество предметов мебели, переданное в этот
        // метод, затем умножить его на сорок пять, поскольку в данном расчёте
        // для каждого предмета предполагается сорок пять минут работы, и после
        // этого сохранить полученное число минут в поле для расчёта стоимости.
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
