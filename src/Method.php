<?php
namespace CCVOnlinePayments\Lib;

class Method {

    private $id;

    private $issuerKey;
    private $issuers;

    private $refundSupported;

    public function __construct($id, $issuerKey = null, $issuers = null, bool $refundSupported)
    {
        $this->id           = $id;
        $this->issuerKey    = $issuerKey;
        $this->issuers      = $issuers;
        $this->refundSupported    = $refundSupported;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->id;
    }

    public function isRefundSupported(): bool
    {
        return $this->refundSupported;
    }

    /**
     * @return string
     */
    public function getIssuerKey() {
        return $this->issuerKey;
    }

    /**
     * @return Issuer[]
     */
    public function getIssuers() {
        return $this->issuers;
    }

    public function isCurrencySupported($currency) {
        $currency = strtoupper($currency);

        return in_array($currency,["EUR", "CHF", "GBP"]);
    }

}
