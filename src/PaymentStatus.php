<?php
namespace CCVOnlinePayments\Lib;

class PaymentStatus {

    const STATUS_PENDING                        = 'pending';
    const STATUS_FAILED                         = 'failed';
    const STATUS_MANUAL_INTERVENTION            = 'manualintervention';
    const STATUS_SUCCESS                        = 'success';

    const FAILURE_CODE_EXPIRED                  = "expired";
    const FAILURE_CODE_CANCELLED                = "cancelled";
    const FAILURE_CODE_UNSUFFICIENT_BALANCE     = "unsufficient_balance";
    const FAILURE_CODE_FRAUD_DETECTED           = "fraud_detected";
    const FAILURE_CODE_REJECTED                 = "rejected";
    const FAILURE_CODE_CARD_REFUSED             = "card_refused";
    const FAILURE_CODE_INSUFFICIENT_FUNDS       = "insufficient_funds";

    private $amount;
    private $status;
    private $failureCode;

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

    public function getFailureCode()
    {
        return $this->failureCode;
    }

    public function setFailureCode($failureCode)
    {
        $this->failureCode = $failureCode;
    }

}
