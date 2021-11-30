<?php
namespace CCVOnlinePayments\Lib;

class Method {

    private $id;

    private $issuerKey;
    private $issuers;

    private $refundSupported;

    public function __construct($id, $issuerKey = null, $issuers = null, bool $refundSupported = false)
    {
        $this->id                        = $id;
        $this->issuerKey                 = $issuerKey;
        $this->issuers                   = $issuers;
        $this->refundSupported           = $refundSupported;
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

    public function isTransactionTypeSaleSupported() {
        return $this->id !== "klarna";
    }

    public function isTransactionTypeAuthoriseSupported() {
        return $this->id === "klarna";
    }

    public function isOrderLinesRequired() {
        return $this->id === "klarna";
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
