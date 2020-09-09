<?php
namespace CCVOnlinePayments\Lib;

class Issuer {

    private $id;
    private $description;

    private $groupType;
    private $groupValue;

    public function __construct($id, $description = null, $groupType = null, $groupValue = null)
    {
        $this->id           = $id;
        $this->description  = $description;
        $this->groupType    = $groupType;
        $this->groupValue   = $groupValue;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getGroupType() {
        return $this->groupType;
    }

    public function getGroupValue() {
        return $this->groupValue;
    }

}
