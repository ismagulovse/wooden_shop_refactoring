<?php

class OrderCancellationRule
{
    private $minutes = 15;

    public function canCancel($order, $now)
    {
        if ($order['deleted_at'] !== null) {
            return false;
        }

        if ($order['status'] !== 'new') {
            return false;
        }

        $seconds = $this->getRemainingSeconds($order['created_at'], $now);
        return $seconds > 0;
    }

    public function getRemainingSeconds($createdAt, $now)
    {
        $deadline = strtotime($createdAt) + $this->minutes * 60;
        $seconds = $deadline - strtotime($now);

        if ($seconds < 0) {
            return 0;
        }

        return $seconds;
    }
}
