<?php
namespace CCVOnlinePayments\Lib;

use CCVOnlinePayments\Lib\Exception\ApiException;
use CCVOnlinePayments\Lib\Exception\InvalidApiKeyException;
use Curl\Curl;
use Psr\Log\LoggerInterface;

class CcvOnlinePaymentsApi {

    const API_ROOT = "https://api.psp.ccv.eu/";

    private $apiRoot;

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
        $this->apiRoot  = self::API_ROOT;
    }

    public function setApiRoot(string $apiRoot) {
        $this->apiRoot = $apiRoot;
    }

    public function setMetadata($metadata) {
        $this->metadata = $metadata;
    }

    public function addMetadata($metadata) {
        if(is_array($this->metadata)) {
            $this->metadata = array_merge($this->metadata, $metadata);
        }else{
            $this->metadata = $metadata;
        }
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

    public function getMethodById($methodId) {
        foreach($this->getMethods() as $method) {
            if($method->getId() === $methodId) {
                return $method;
            }
        }

        return null;
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
                    $methods[] = new Method("card_".$issuer->getId(), null, null, true);
                }
            }else {
                if($methodId === "ideal") {
                    $issuerKey = "issuerid";
                    $issuers = $this->parseIssuers($responseMethod, $issuerKey, "issuerdescription", "grouptype", "group");
                }elseif($methodId === "ideal") {
                    $issuerKey = "issuerid";
                    $issuers = $this->parseIssuers($responseMethod, $issuerKey, "issuerdescription",null, null);
                }

                $methods[] = new Method($methodId, $issuerKey, $issuers, !in_array($methodId, ['landingpage','terminal', 'token', 'vault']));
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
            "klarna",
            "paypal",
            "card_amex",
            "sofort",
            "giropay",
            "banktransfer",
            "applepay",
            "googlepay"
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
            list($method, $brand) = explode("_", $request->getMethod());
        }else{
            $method = $request->getMethod();
            $brand  = $request->getBrand();
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
            "issuer"                    => $request->getIssuer(),
            "brand"                     => $brand,
            "metadata"                  => $this->getMetadataString(),
            "scaReady"                  => $request->getScaReady(),
            "billingAddress"            => $request->getBillingAddress(),
            "billingCity"               => $request->getBillingCity(),
            "billingState"              => $request->getBillingState(),
            "billingPostalCode"         => $request->getBillingPostalCode(),
            "billingCountry"            => $request->getBillingCountry(),
            "billingEmail"              => $request->getBillingEmail(),
            "billingHouseNumber"        => $request->getBillingHouseNumber(),
            "billingHouseExtension"     => $request->getBillingHouseExtension(),
            "billingPhoneNumber"        => $request->getBillingPhoneNumber(),
            "billingPhoneCountry"       => $request->getBillingPhoneCountry(),
            "billingFirstName"          => $request->getBillingFirstName(),
            "billingLastName"           => $request->getBillingLastName(),
            "shippingAddress"           => $request->getShippingAddress(),
            "shippingCity"              => $request->getShippingCity(),
            "shippingState"             => $request->getShippingState(),
            "shippingPostalCode"        => $request->getShippingPostalCode(),
            "shippingCountry"           => $request->getShippingCountry(),
            "shippingHouseNumber"       => $request->getShippingHouseNumber(),
            "shippingHouseExtension"    => $request->getShippingHouseExtension(),
            "shippingEmail"             => $request->getShippingEmail(),
            "shippingFirstName"         => $request->getShippingFirstName(),
            "shippingLastName"          => $request->getShippingLastName(),
            "transactionType"           => $request->getTransactionType(),
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
            ],
            "details"           => $request->getDetails()
        ];

        if($request->getOrderLines() !== null) {
            $requestData["orderLines"] = [];
            foreach($request->getOrderLines() as $orderLine) {
                $requestData["orderLines"][] = $this->getDataByOrderLine($orderLine);
            }
        }

        $this->removeNullAndFormat($requestData);

        $apiResponse = $this->apiPost("api/v1/payment", $requestData);

        $paymentResponse = new PaymentResponse();
        $paymentResponse->setReference($apiResponse->reference);

        if(isset($apiResponse->payUrl)) {
            $paymentResponse->setPayUrl($apiResponse->payUrl);
        }else{
            $paymentResponse->setPayUrl($apiResponse->returnUrl);
        }
        return $paymentResponse;
    }

    private function getDataByOrderLine(OrderLine $orderLine) {
        return [
            "type"          => $orderLine->getType(),
            "name"          => $orderLine->getName(),
            "code"          => $orderLine->getCode(),
            "quantity"      => $orderLine->getQuantity(),
            "unit"          => $orderLine->getUnit(),
            "unitPrice"     => $orderLine->getUnitPrice(),
            "totalPrice"    => $orderLine->getTotalPrice(),
            "discount"      => $orderLine->getDiscount(),
            "vatRate"       => $orderLine->getVatRate(),
            "vat"           => $orderLine->getVat(),
            "url"           => $orderLine->getUrl(),
            "imageUrl"      => $orderLine->getImageUrl(),
            "brand"         => $orderLine->getBrand(),
        ];
    }

    /**
     * @param RefundRequest $request
     * @return RefundResponse
     */
    public function createRefund(RefundRequest $request) {
        $requestData = [
            "reference" => $request->getReference()
        ];

        if($request->getDescription() !== null) {
            $requestData["description"] =  $request->getDescription();
        }

        if($request->getAmount() !== null) {
            $requestData["amount"] = number_format($request->getAmount(),2,".","");
        }

        if($request->getOrderLines() !== null) {
            $requestData["orderLines"] = [];
            foreach($request->getOrderLines() as $orderLine) {
                $requestData["orderLines"][] = $this->getDataByOrderLine($orderLine);
            }
        }

        $this->removeNullAndFormat($requestData);

        $apiResponse = $this->apiPost("api/v1/refund", $requestData, $request->getIdempotencyReference());

        $refundResponse = new RefundResponse();
        $refundResponse->setReference($apiResponse->reference);
        return $refundResponse;
    }

    /**
     * @param CaptureRequest $request
     * @return CaptureResponse
     */
    public function createCapture(CaptureRequest $request) {
        $requestData = [
            "reference" => $request->getReference()
        ];

        if($request->getAmount() !== null) {
            $requestData["amount"] = number_format($request->getAmount(),2,".","");
        }

        if($request->getOrderLines() !== null) {
            $requestData["orderLines"] = [];
            foreach($request->getOrderLines() as $orderLine) {
                $requestData["orderLines"][] = $this->getDataByOrderLine($orderLine);
            }
        }

        $this->removeNullAndFormat($requestData);

        $apiResponse = $this->apiPost("api/v1/capture", $requestData, $request->getIdempotencyReference());

        $captureResponse = new CaptureResponse();
        $captureResponse->setReference($apiResponse->reference);
        return $captureResponse;
    }

    /**
     * @param ReversalRequest $request
     * @return ReversalResponse
     */
    public function createReversal(ReversalRequest $request) {
        $requestData = [
            "reference" => $request->getReference()
        ];

        $this->removeNullAndFormat($requestData);

        $apiResponse = $this->apiPost("api/v1/reversal", $requestData, $request->getIdempotencyReference());

        $reversalResponse = new ReversalResponse();
        $reversalResponse->setReference($apiResponse->reference);
        return $reversalResponse;
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
        $paymentStatus->setFailureCode($apiResponse->failureCode ?? null);
        $paymentStatus->setTransactionType($apiResponse->type);
        $paymentStatus->setDetails($apiResponse->details??(new \stdClass()));

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

    private function apiGet(string $endpoint, array $parameters, ?string $idempotencyReference = null) {
        return $this->apiCall("get", $endpoint, $parameters, $idempotencyReference);
    }

    private function apiPost(string $endpoint, array $parameters, ?string $idempotencyReference = null) {
        return $this->apiCall("post", $endpoint, $parameters, $idempotencyReference);
    }

    private function apiCall(string $method, string $endpoint, array $originalParameters, ?string $idempotencyReference = null, int $attempt = 0) {
        $curl = new Curl();
        $curl->setBasicAuthentication($this->apiKey);
        $curl->setOpt(CURLINFO_HEADER_OUT, true);

        if($method === "post") {
            $curl->setHeader("Content-Type", "application/json");
            $parameters = json_encode($originalParameters);
        }else{
            $parameters = $originalParameters;
        }

        if($idempotencyReference) {
            $curl->setHeader("Idempotency-Reference", $idempotencyReference);
        }

        $curl->$method($this->apiRoot.$endpoint, $parameters);

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

        if($statusCode == 429 && $attempt < 3) {
            $this->logger->warning("CCV Online Payments api request - Too many requests", $loggingContext);
            sleep(2*($attempt+1));
            return $this->apiCall($method, $endpoint, $originalParameters, $idempotencyReference, $attempt++);
        }else if($statusCode >= 200 && $statusCode < 300) {
            $this->logger->debug("CCV Online Payments api request", $loggingContext);
        }else{
            $this->logger->error("CCV Online Payments api request error", $loggingContext);
        }

        if($statusCode >= 200 && $statusCode < 300) {
            return $response;
        }elseif($statusCode == 401) {
            throw new InvalidApiKeyException($curl->rawResponse);
        }else{
            throw new ApiException($curl->rawResponse);
        }
    }
}
