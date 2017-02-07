<?php

namespace app\models;

use yii\base\Model;

class RuntimeParameters extends Model {

    private $data;

    public function __construct(array $data) {
        $this->data = $data;
        parent::__construct();
    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        return null;
    }

    public function offsetUnset($offset) {
        return null;
    }

}
