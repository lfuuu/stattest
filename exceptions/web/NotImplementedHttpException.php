<?php
namespace app\exceptions\web;


class NotImplementedHttpException extends \yii\web\HttpException
{
    public function __construct()
    {
        parent::__construct(501, "Not Implemented", 501);
    }
}
