<?php

namespace app\workers;

class OperationSystem {

    protected $output = [];
    protected $returnVar = 0;

    public function execute($command, array $params) {
        $this->output = [];
        $params = array_map('escapeshellarg', $params);
        array_unshift($params, $command);
        $string = call_user_func_array('sprintf', $params);
        exec($string, $this->output, $this->returnVar);
        return $this->returnVar;
    }

    public function getStringOutput() {
        return implode(PHP_EOL, $this->output);
    }

    public function getArrayOutput() {
        return $this->output;
    }

    public function getReturnVar() {
        return $this->returnVar;
    }

}
