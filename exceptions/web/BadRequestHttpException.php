<?php
namespace app\exceptions\web;


class BadRequestHttpException extends \yii\web\BadRequestHttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct()
    {
        parent::__construct("Bad Request");
    }
}
