<?php
namespace CCVOnlinePayments\Lib;

class PaymentResponse {

    private $reference;
    private $payUrl;

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getPayUrl()
    {
        return $this->payUrl;
    }

    public function setPayUrl($payUrl)
    {
        $this->payUrl = $payUrl;
    }
}
