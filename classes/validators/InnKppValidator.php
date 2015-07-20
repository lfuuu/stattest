<?php
namespace app\classes\validators;

use app\models\ClientContract;
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

    public function validateAttributes($model)
    {
        $attributes = [];
        if(in_array($model->legal_type,  ['ip', 'legal']))
            $attributes[] = 'inn';
        if($model->legal_type == 'legal')
            $attributes[] = 'kpp';

        $hasCheckedContracts = $this->hasCheckedContract($model);

        if($attributes && ($hasCheckedContracts || $model->inn || $model->kpp)){
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
    }

    private function hasCheckedContract($model)
    {
        return ClientContract::find()
            ->andWhere(['contragent_id' =>$model->id])
            ->andWhere(['!=', 'state', 'unchecked'])
            ->count() ? true : false;
    }

    protected function checkUnique($model, $attributes)
    {
        $query = $model::find();

        $labels = [];
        foreach($attributes as $attribute) {
            $labels[] = $model->getAttributeLabel($attribute);
            $query->andWhere([$attribute => $model->$attribute]);
        }
        $query->andWhere(['!=', 'id', $model->id]);
        $models = $query->all();

        if($models) {
            foreach($attributes as $attribute)
                $this->addError($model, $attribute, '{attrs} must be unique', ['attrs' => implode(', ', $labels)]);
                //$this->addError($model, $attribute, 'Связка {attrs} должна быть уникальной', ['attrs' => implode(', ', $labels)]);
        }

        $double = $model::find()
            ->andWhere(['inn' => $model->inn])
            ->andWhere(['!=', 'super_id', $model->super_id])
            ->one();
        if($double)
            $this->addError($model, 'inn', 'Inn is already in another client <a href="/contragent/edit?id={contragentId}" target="_blank">контрагента</a>', ['contragentId' => $double->id]);
    }
}