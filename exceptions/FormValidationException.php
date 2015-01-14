<?php
namespace app\exceptions;

use yii\base\Model;
use yii\web\HttpException;

class FormValidationException extends HttpException
{
    private $errors = [];

    public function __construct(Model $form)
    {
        $this->errors = $form->getErrors();
        parent::__construct(400);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}