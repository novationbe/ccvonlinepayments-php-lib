<?php
namespace CCVOnlinePayments\Lib;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;

class PaymentRequest {

    public const TRANSACTION_TYPE_SALE      = "sale";
    public const TRANSACTION_TYPE_CREDIT    = "credit";
    public const TRANSACTION_TYPE_AUTHORIZE = "authorise";

    private $currency;
    private $amount;
    private $returnUrl;
    private $method;
    private $merchantOrderReference;
    private $description;
    private $webhookUrl;
    private $issuer;
    private $brand;
    private $language;

    private $scaReady;

    private $billingAddress;
    private $billingCity;
    private $billingState;
    private $billingPostalCode;
    private $billingCountry;
    private $billingHouseNumber;
    private $billingHouseExtension;
    private $billingPhoneNumber;
    private $shippingAddress;
    private $shippingCity;
    private $shippingState;
    private $shippingPostalCode;
    private $shippingCountry;
    private $shippingHouseNumber;
    private $shippingHouseExtension;

    private $transactionType;

    private $accountInfo_accountIdentifier;
    private $accountInfo_accountCreationDate;
    private $accountInfo_accountChangeDate;
    private $accountInfo_email;
    private $accountInfo_homePhoneNumber;
    private $accountInfo_mobilePhoneNumber;

    private $merchantRiskIndicator_deliveryEmailAddress;

    private $browser_acceptHeaders;
    private $browser_ipAddress;
    private $browser_language;
    private $browser_userAgent;

    private $details;

    private $orderLines = [];

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getMerchantOrderReference()
    {
        return $this->merchantOrderReference;
    }

    public function setMerchantOrderReference($merchantOrderReference)
    {
        $this->merchantOrderReference = $merchantOrderReference;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getWebhookUrl()
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getScaReady()
    {
        return $this->scaReady;
    }

    public function setScaReady($scaReady)
    {
        $this->scaReady = $scaReady;
    }

    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    public function getBillingCity()
    {
        return $this->billingCity;
    }

    public function setBillingCity($billingCity)
    {
        $this->billingCity = $billingCity;
    }

    public function getBillingState()
    {
        return $this->billingState;
    }

    public function setBillingState($billingState)
    {
        $this->billingState = $billingState;
    }

    public function getBillingPostalCode()
    {
        return $this->billingPostalCode;
    }

    public function setBillingPostalCode($billingPostalCode)
    {
        $this->billingPostalCode = $billingPostalCode;
    }

    public function getBillingCountry()
    {
        if($this->billingCountry === "NL") {
            return "NLD";
        }

        return $this->billingCountry;
    }

    public function setBillingCountry($billingCountry)
    {
        $this->billingCountry = $billingCountry;
    }

    public function getBillingHouseNumber()
    {
        return $this->billingHouseNumber;
    }

    public function setBillingHouseNumber($billingHouseNumber)
    {
        $this->billingHouseNumber = $billingHouseNumber;
    }

    public function getBillingHouseExtension()
    {
        return $this->billingHouseExtension;
    }

    public function setBillingHouseExtension($billingHouseExtension)
    {
        $this->billingHouseExtension = $billingHouseExtension;
    }

    public function getBillingPhoneNumber()
    {
        return $this->getPhoneNumber($this->billingPhoneNumber, $this->billingCountry);
    }

    public function getBillingPhoneCountry()
    {
        return $this->getPhoneCountryNumber($this->billingPhoneNumber, $this->billingCountry);
    }

    public function setBillingPhoneNumber($billingPhoneNumber)
    {
        $this->billingPhoneNumber = $billingPhoneNumber;
    }

    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    public function getShippingCity()
    {
        return $this->shippingCity;
    }

    public function setShippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;
    }

    public function getShippingState()
    {
        return $this->shippingState;
    }

    public function setShippingState($shippingState)
    {
        $this->shippingState = $shippingState;
    }

    public function getShippingPostalCode()
    {
        return $this->shippingPostalCode;
    }

    public function setShippingPostalCode($shippingPostalCode)
    {
        $this->shippingPostalCode = $shippingPostalCode;
    }

    public function getShippingCountry()
    {
        if($this->shippingCountry === "NL") {
            return "NLD";
        }

        return $this->shippingCountry;
    }

    public function setShippingCountry($shippingCountry)
    {
        $this->shippingCountry = $shippingCountry;
    }

    public function getShippingHouseNumber()
    {
        return $this->shippingHouseNumber;
    }

    public function setShippingHouseNumber($shippingHouseNumber)
    {
        $this->shippingHouseNumber = $shippingHouseNumber;
    }

    public function getShippingHouseExtension()
    {
        return $this->shippingHouseExtension;
    }

    public function setShippingHouseExtension($shippingHouseExtension)
    {
        $this->shippingHouseExtension = $shippingHouseExtension;
    }

    public function getAccountInfoAccountIdentifier()
    {
        return $this->accountInfo_accountIdentifier;
    }

    public function setAccountInfoAccountIdentifier($accountInfo_accountIdentifier)
    {
        $this->accountInfo_accountIdentifier = $accountInfo_accountIdentifier;
    }

    public function getAccountInfoAccountCreationDate()
    {
        return $this->accountInfo_accountCreationDate;
    }

    public function setAccountInfoAccountCreationDate($accountInfo_accountCreationDate)
    {
        if($accountInfo_accountCreationDate instanceof \DateTime) {
            $this->accountInfo_accountCreationDate = $accountInfo_accountCreationDate;
        }else{
            $this->accountInfo_accountCreationDate = null;
        }
    }

    public function getAccountInfoAccountChangeDate()
    {
        return $this->accountInfo_accountChangeDate;
    }

    public function setAccountInfoAccountChangeDate($accountInfo_accountChangeDate)
    {
        if($accountInfo_accountChangeDate instanceof \DateTime) {
            $this->accountInfo_accountChangeDate = $accountInfo_accountChangeDate;
        }else{
            $this->accountInfo_accountChangeDate = null;
        }
    }

    public function getAccountInfoEmail()
    {
        return $this->accountInfo_email;
    }

    public function setAccountInfoEmail($accountInfo_email)
    {
        $this->accountInfo_email = $accountInfo_email;
    }

    public function getAccountInfoHomePhoneNumber()
    {
        return $this->getPhoneNumber($this->accountInfo_homePhoneNumber, null);
    }

    public function getAccountInfoHomePhoneCountry()
    {
        return $this->getPhoneCountryNumber($this->accountInfo_homePhoneNumber, null);
    }

    public function setAccountInfoHomePhoneNumber($accountInfo_homePhoneNumber)
    {
        $this->accountInfo_homePhoneNumber = $accountInfo_homePhoneNumber;
    }

    public function getAccountInfoMobilePhoneNumber()
    {
        return $this->getPhoneNumber($this->accountInfo_mobilePhoneNumber, null);
    }

    public function getAccountInfoMobilePhoneCountry()
    {
        return $this->getPhoneCountryNumber($this->accountInfo_mobilePhoneNumber, null);
    }

    public function setAccountInfoMobilePhoneNumber($accountInfo_mobilePhoneNumber)
    {
        $this->accountInfo_mobilePhoneNumber = $accountInfo_mobilePhoneNumber;
    }

    public function getMerchantRiskIndicatorDeliveryEmailAddress()
    {
        return $this->merchantRiskIndicator_deliveryEmailAddress;
    }

    public function setMerchantRiskIndicatorDeliveryEmailAddress($merchantRiskIndicator_deliveryEmailAddress)
    {
        $this->merchantRiskIndicator_deliveryEmailAddress = $merchantRiskIndicator_deliveryEmailAddress;
    }

    public function getBrowserAcceptHeaders()
    {
        return $this->browser_acceptHeaders;
    }

    public function setBrowserAcceptHeaders($browser_acceptHeaders)
    {
        $this->browser_acceptHeaders = $browser_acceptHeaders;
    }

    public function getBrowserIpAddress()
    {
        return $this->browser_ipAddress;
    }

    public function setBrowserIpAddress($browser_ipAddress)
    {
        $this->browser_ipAddress = $browser_ipAddress;
    }

    public function getBrowserLanguage()
    {
        return $this->browser_language;
    }

    public function setBrowserLanguage($browser_language)
    {
        $this->browser_language = $browser_language;
    }

    public function getBrowserUserAgent()
    {
        return $this->browser_userAgent;
    }

    public function setBrowserUserAgent($browser_userAgent)
    {
        $this->browser_userAgent = $browser_userAgent;
    }

    public function setBrowserFromServer() {
        $this->setBrowserAcceptHeaders(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null);
        $this->setBrowserIpAddress(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        $this->setBrowserLanguage(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null);
        $this->setBrowserUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
    }

    private function getPhoneNumber($phoneNumber, $countryCode) {
        try {
            $number = PhoneNumber::parse($phoneNumber, $countryCode);
            return $number->getNationalNumber();
        }catch(PhoneNumberParseException $phoneNumberParseException) {
            return null;
        }
    }

    private function getPhoneCountryNumber($phoneNumber, $countryCode) {
        try {
            $number = PhoneNumber::parse($phoneNumber, $countryCode);
            return $number->getCountryCode();
        }catch(PhoneNumberParseException $phoneNumberParseException) {
            return null;
        }
    }

    public function getTransactionType()
    {
        return $this->transactionType;
    }

    public function setTransactionType($transactionType): void
    {
        $this->transactionType = $transactionType;
    }

    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function setOrderLines(array $orderLines): void
    {
        $this->orderLines = $orderLines;
    }

    public function getDetails() : ?array
    {
        return $this->details;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }


}
