<?php
namespace app\classes\validators;

use yii\validators\Validator;

class InnKppValidator extends Validator
{

    protected $attrValidator = [];

    public function init()
    {
        parent::init();
        $this->attrValidator = [
            'inn' => InnValidator::className(),
            'kpp' => KppValidator::className(),
        ];
    }

    public function validateAttributes($model, $attributes = null)
    {
        if (is_array($attributes)) {
            $attributes = array_intersect($this->attributes, $attributes);
        } else {
            $attributes = $this->attributes;
        }

        foreach ($attributes as $attribute) {
            $skip = $this->skipOnError && $model->hasErrors($attribute)
                || $this->skipOnEmpty && $this->isEmpty($model->$attribute);
            if (!$skip) {
                if ($this->when === null || call_user_func($this->when, $model, $attribute)) {
                    self::createValidator($this->attrValidator[$attribute], $model, $attribute)->validateAttributes($model);
                }
            }
        }
        $this->checkUnique($model, $attributes);
    }

    protected function checkUnique($model, $attributes)
    {
        $query = $model::find();

        $labels = [];
        foreach($attributes as $attribute) {
            $labels[] = $model->getAttributeLabel($attribute);
            $query->andWhere([$attribute => $model->$attribute]);
        }
        $models = $query->all();

        if(count($models) > 1) {
            foreach($attributes as $attribute)
                $this->addError($model, $attribute, '{attrs} must be unique', ['attrs' => implode(', ', $labels)]);
                //$this->addError($model, $attribute, 'Связка {attrs} должна быть уникальной', ['attrs' => implode(', ', $labels)]);
        }
    }
}