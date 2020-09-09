<?php
namespace CCVOnlinePayments\Lib;

use CCVOnlinePayments\Lib\Exception\ApiException;
use CCVOnlinePayments\Lib\Exception\InvalidApiKeyException;
use Curl\Curl;
use Psr\Log\LoggerInterface;

class CcvOnlinePaymentsApi {

    const API_ROOT = "https://redirect.jforce.be/";

    private $cache;
    private $logger;
    private $apiKey;
    private $metadata;

    private $methods = null;

    public function __construct(Cache $cache, LoggerInterface $logger, ?string $apiKey)
    {
        $this->cache    = $cache;
        $this->logger   = $logger;
        $this->apiKey   = $apiKey;
    }

    public function setMetadata($metadata) {
        $this->metadata = $metadata;
    }

    public function getMetadataString() {
        if(is_array($this->metadata)) {
            $metadata = $this->metadata;
            $metadata["PHP"] = phpversion();
            $metadata["OS"]  = php_uname();

            $parts = [];
            foreach ($metadata as $key => $value) {
                $parts[] = $key .":".$value;
            }

            $string = implode(";", $parts);
            return substr($string,0,255);
        }

        return "";
    }

    /**
     * @return Method[]
     */
    public function getMethods() {
        if($this->methods === null) {
            $this->methods = $this->cache->getWithFallback("CCVONLINEPAYMENTS_METHODS_" . sha1($this->apiKey), 3600, function () {
                return $this->_getMethods();
            });
        }

        return $this->methods;
    }

    private function _getMethods() {
        $apiResponse = $this->apiGet("api/v1/method", []);

        $methods = [];
        foreach($apiResponse as $responseMethod) {
            $methodId = $responseMethod->method;

            $issuerKey = null;
            $issuers   = null;
            if($methodId === "card") {
                $issuerKey = "brand";
                $issuers = $this->parseIssuers($responseMethod, $issuerKey, $issuerKey, null, null);

                /** @var Issuer $issuer */
                foreach($issuers as $issuer) {
                    $methods[] = new Method("card_".$issuer->getId(), null, null);
                }
            }else {
                if($methodId === "ideal") {
                    $issuerKey = "issuerid";
                    $issuers = $this->parseIssuers($responseMethod, $issuerKey, "issuerdescription", "grouptype", "group");
                }elseif($methodId === "ideal") {
                    $issuerKey = "issuerid";
                    $issuers = $this->parseIssuers($responseMethod, $issuerKey, "issuerdescription",null, null);
                }

                $methods[] = new Method($methodId, $issuerKey, $issuers);
            }
        }

        return $methods;
    }

    public function sortMethods($methods, $countryCode = null) {
        $methodOrder = array_flip(self::getSortedMethodIds($countryCode));

        usort($methods, function($a, $b) use($methodOrder){
            $aOrder = $methodOrder[$a->getId()] ?? 999;
            $bOrder = $methodOrder[$b->getId()] ?? 999;

            if($aOrder === $bOrder) {
                return strcmp($a->getId(), $b->getId());
            }else{
                return $aOrder <=> $bOrder;
            }
        });

        return $methods;
    }

    public static function getSortedMethodIds($countryCode = null) {
        $methodIds = [
            "ideal",
            "card_bcmc",
            "card_maestro",
            "card_mastercard",
            "card_visa",
            "paypal",
            "card_amex",
            "sofort",
            "giropay",
            "banktransfer"
        ];

        if(strtoupper($countryCode) === "BE") {
            $methodIds[0] = "card_bcmc";
            $methodIds[1] = "ideal";
        }

        return $methodIds;
    }

    public function isKeyValid() {
        try {
            $this->getMethods();
        }catch(InvalidApiKeyException $invalidApiKeyException) {
            return false;
        }

        return true;
    }

    /**
     * @param PaymentRequest $request
     * @return PaymentResponse
     */
    public function createPayment($request) {
        if(strpos($request->getMethod(),"card_") === 0) {
            list($method, $issuer) = explode("_", $request->getMethod());
        }else{
            $method = $request->getMethod();
            $issuer = $request->getIssuer();
        }

        $requestData = [
            "amount"                    => number_format($request->getAmount(),2,".",""),
            "currency"                  => $request->getCurrency(),
            "returnUrl"                 => $request->getReturnUrl(),
            "method"                    => $method,
            "language"                  => $request->getLanguage(),
            "merchantOrderReference"    => $request->getMerchantOrderReference(),
            "description"               => $request->getDescription(),
            "webhookUrl"                => $request->getWebhookUrl(),
            "issuer"                    => $issuer,
            "brand"                     => $request->getBrand(),
            "metadata"                  => $this->getMetadataString(),
            "scaReady"                  => $request->getScaReady(),
            "billingAddress"            => $request->getBillingAddress(),
            "billingCity"               => $request->getBillingCity(),
            "billingState"              => $request->getBillingState(),
            "billingPostalCode"         => $request->getBillingPostalCode(),
            "billingCountry"            => $request->getBillingCountry(),
            "billingHouseNumber"        => $request->getBillingHouseNumber(),
            "billingHouseExtension"     => $request->getBillingHouseExtension(),
            "billingPhoneNumber"        => $request->getBillingPhoneNumber(),
            "billingPhoneCountry"       => $request->getBillingPhoneCountry(),
            "shippingAddress"           => $request->getShippingAddress(),
            "shippingCity"              => $request->getShippingCity(),
            "shippingState"             => $request->getShippingState(),
            "shippingPostalCode"        => $request->getShippingPostalCode(),
            "shippingCountry"           => $request->getShippingCountry(),
            "shippingHouseNumber"       => $request->getShippingHouseNumber(),
            "shippingHouseExtension"    => $request->getShippingHouseExtension(),
            "accountInfo" => [
                "accountIdentifier"     =>  $request->getAccountInfoAccountIdentifier(),
                "accountCreationDate"   =>  $request->getAccountInfoAccountCreationDate(),
                "accountChangedDate"    =>  $request->getAccountInfoAccountChangeDate(),
                "email"                 =>  $request->getAccountInfoEmail(),
                "homePhoneNumber"       =>  $request->getAccountInfoHomePhoneNumber(),
                "homePhoneCountry"      =>  $request->getAccountInfoHomePhoneCountry(),
                "mobilePhoneNumber"     =>  $request->getAccountInfoMobilePhoneNumber(),
                "mobilePhoneCountry"    =>  $request->getAccountInfoMobilePhoneCountry(),
            ],
            "merchantRiskIndicator" => [
                "deliveryEmailAddress"  => $request->getMerchantRiskIndicatorDeliveryEmailAddress()
            ],
            "browser" => [
                "acceptHeaders" => $request->getBrowserAcceptHeaders(),
                "language"      => $request->getBrowserLanguage(),
                "ipAddress"     => $request->getBrowserIpAddress(),
                "userAgent"     => $request->getBrowserUserAgent()
            ]
        ];

        $this->removeNullAndFormat($requestData);

        $apiResponse = $this->apiPost("api/v1/payment", $requestData);

        $paymentResponse = new PaymentResponse();
        $paymentResponse->setReference($apiResponse->reference);
        $paymentResponse->setPayUrl($apiResponse->payUrl);
        return $paymentResponse;
    }

    private function removeNullAndFormat(&$array) {
        foreach($array as $key => &$value) {
            if($value === null) {
                unset($array[$key]);
            }elseif(is_array($value)) {
                $this->removeNullAndFormat($value);
                if(sizeof($value) === 0) {
                    unset($array[$key]);
                }
            }elseif($value instanceof \DateTime) {
                $value = $value->format("Ymd");
            }
        }
    }

    public function getPaymentStatus($paymentReference) {
        $apiResponse = $this->apiGet("api/v1/transaction", ["reference" => $paymentReference]);

        $paymentStatus = new PaymentStatus();
        $paymentStatus->setAmount($apiResponse->amount);
        $paymentStatus->setStatus($apiResponse->status);

        return $paymentStatus;
    }

    private function parseIssuers($method, $issuerKey, $descriptionKey, $groupTypeKey, $groupValueKey) {
        if(isset($method->options)) {
            $issuers = [];

            foreach($method->options as $option) {
                $issuers[] = new Issuer(
                    $option->{$issuerKey},
                    $option->{$descriptionKey},
                    $groupTypeKey !== null ? $option->{$groupTypeKey} : null,
                    $groupTypeKey !== null ? $option->{$groupValueKey} : null
                );
            }

            return $issuers;
        }

        return null;
    }

    private function apiGet(string $endpoint, array $parameters) {
        return $this->apiCall("get", $endpoint, $parameters);
    }

    private function apiPost(string $endpoint, array $parameters) {
        return $this->apiCall("post", $endpoint, $parameters);
    }

    private function apiCall(string $method, string $endpoint, array $parameters) {
        $curl = new Curl();
        $curl->setBasicAuthentication($this->apiKey);
        $curl->setOpt(CURLINFO_HEADER_OUT, true);

        if($method === "post") {
            $curl->setHeader("Content-Type", "application/json");
            $parameters = json_encode($parameters);
        }

        $curl->$method(self::API_ROOT.$endpoint, $parameters);

        $requestHeaders = [];
        foreach($curl->getRequestHeaders() as $key => $value) {
            $requestHeaders[$key] = $value;
        }

        $responseHeaders = [];
        foreach($curl->getResponseHeaders() as $key => $value) {
            $responseHeaders[$key] = $value;
        }

        $loggingContext = [
            "method"          => $method,
            "endpoint"        => $endpoint,
            "parameters"      => $parameters,
            "statusCode"      => $curl->getHttpStatusCode(),
            "requestHeaders"  => $requestHeaders,
            "responseHeaders" => $responseHeaders,
            "response"        => $curl->getRawResponse()
        ];

        $statusCode = $curl->getHttpStatusCode();
        $response = $curl->response;
        $curl->close();

        if($statusCode >= 200 && $statusCode < 300) {
            $this->logger->debug("CCV Online Payments api request", $loggingContext);
        }else{
            $this->logger->error("CCV Online Payments api request error", $loggingContext);
        }

        if($statusCode >= 200 && $statusCode < 300) {
            return $response;
        }elseif($curl->httpStatusCode == 401) {
            throw new InvalidApiKeyException($curl->rawResponse);
        }else{
            throw new ApiException($curl->rawResponse);
        }
    }
}
