<?php
namespace app\exceptions\web;


class BadRequestHttpException extends \yii\web\BadRequestHttpException
{
    /**
     * Constructor.
     *
     * @param string $message error message
     */
    public function __construct($message = '')
    {
        parent::__construct($message ?: 'Bad Request');
    }
}
