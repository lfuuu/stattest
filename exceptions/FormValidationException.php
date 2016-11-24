<?php
namespace app\exceptions;

use yii\base\Model;
use yii\web\HttpException;

class FormValidationException extends HttpException
{
    private $errors = [];

    /**
     * @param Model $form
     */
    public function __construct(Model $form)
    {
        $this->errors = $form->getErrors();
        parent::__construct(400, reset($form->getFirstErrors()));
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}