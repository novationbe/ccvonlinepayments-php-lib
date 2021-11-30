<?php
namespace CCVOnlinePayments\Lib;


class CaptureRequest {

    private $reference;
    private $amount;
    private $idempotencyReference;

    private $orderLines = [];

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getIdempotencyReference()
    {
        return $this->idempotencyReference;
    }

    public function setIdempotencyReference($idempotencyReference): void
    {
        $this->idempotencyReference = $idempotencyReference;
    }

    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function setOrderLines(array $orderLines): void
    {
        $this->orderLines = $orderLines;
    }


}
