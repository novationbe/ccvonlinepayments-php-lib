<?php
namespace CCVOnlinePayments\Lib;

class PaymentStatus {

    const STATUS_PENDING                = 'pending';
    const STATUS_FAILED                 = 'failed';
    const STATUS_MANUAL_INTERVENTION    = 'manualintervention';
    const STATUS_SUCCESS                = 'success';

    private $amount;
    private $status;

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
