<?php
namespace CCVOnlinePayments\Lib;

class Method {

    private $id;

    private $issuerKey;
    private $issuers;

    public function __construct($id, $issuerKey = null, $issuers = null)
    {
        $this->id           = $id;
        $this->issuerKey    = $issuerKey;
        $this->issuers      = $issuers;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->id;
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
