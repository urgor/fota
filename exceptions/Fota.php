<?php

namespace app\exceptions;

use yii;
use yii\base\ExitException;

class Fota extends ExitException
{
    public function __construct($name, $message = null, $code = 0, $status = 400, \Exception $previous = null)
    {
        $response = yii::$app->getResponse();
        $response->data = [
            'error' => true,
            'msg' => $name,
        ];

        $response->setStatusCode($status);

        parent::__construct($status, $message, $code, $previous);
    }
}