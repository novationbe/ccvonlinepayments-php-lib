<?php
namespace CCVOnlinePayments\Lib;

abstract class Cache {

    public abstract function set(string $key, string $value, int $timeout) : void;
    public abstract function get(string $key) : ?string;

    public function getWithFallback(string $key, int $timeout, callable $function) {
        $stringValue = $this->get($key);
        if($stringValue === null) {
            $value = $function();
            $this->set($key, serialize($value), $timeout);
        }else{
            $value = unserialize($stringValue);
        }

        return $value;
    }

}
