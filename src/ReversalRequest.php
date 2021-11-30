<?php
namespace CCVOnlinePayments\Lib;


class ReversalRequest {

    private $reference;

    private $idempotencyReference;

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getIdempotencyReference()
    {
        return $this->idempotencyReference;
    }

    public function setIdempotencyReference($idempotencyReference): void
    {
        $this->idempotencyReference = $idempotencyReference;
    }
}
