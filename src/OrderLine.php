<?php
namespace CCVOnlinePayments\Lib;

class OrderLine {

    public const TYPE_PHYSICAL      = "PHYSICAL";
    public const TYPE_DISCOUNT      = "DISCOUNT";
    public const TYPE_DIGITAL       = "DIGITAL";
    public const TYPE_SHIPPING_FEE  = "SHIPPING_FEE";
    public const TYPE_STORE_CREDIT  = "STORE_CREDIT";
    public const TYPE_GIFT_CARD     = "GIFT_CARD";
    public const TYPE_SURCHARGE     = "SURCHARGE";
    public const TYPE_SALES_TAX     = "SALES_TAX";
    public const TYPE_DEPOSIT       = "DEPOSIT";

    private $type;
    private $name;
    private $code;
    private $quantity;
    private $unit;
    private $unitPrice;
    private $totalPrice;
    private $discount;
    private $vatRate;
    private $vat;
    private $url;
    private $imageUrl;
    private $brand;

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code): void
    {
        $this->code = $code;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    public function setUnitPrice($unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function setTotalPrice($totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount): void
    {
        $this->discount = $discount;
    }

    public function getVatRate()
    {
        return $this->vatRate;
    }

    public function setVatRate($vatRate): void
    {
        $this->vatRate = $vatRate;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function setVat($vat): void
    {
        $this->vat = $vat;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand): void
    {
        $this->brand = $brand;
    }

}
