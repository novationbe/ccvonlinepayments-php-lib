<?php
namespace CCVOnlinePayments\Lib;


class RefundResponse {

    private $reference;

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

}
