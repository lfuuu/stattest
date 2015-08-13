<?php
namespace app\classes\validators;

use app\models\ClientContract;
use app\models\ContractType;
use app\models\Country;
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
        if (in_array($model->legal_type, ['ip', 'legal']))
            $attributes[] = 'inn';
        if ($model->legal_type == 'legal' && $model->country_id == Country::RUSSIA)
            $attributes[] = 'kpp';

        $contracts = $this->hasOperatorContract($model);
        $hasCheckedContracts = $model->hasChecked || $this->hasCheckedContract($model);

        if ($attributes) {
            $has = false;
            foreach ($attributes as $attribute) {
                if (
                    ($hasCheckedContracts || $model->$attribute)
                    && $this->when === null || call_user_func($this->when, $model, $attribute)
                ) {
                    $has = true;
                    self::createValidator($this->attrValidator[$attribute], $model, $attribute)->validateAttribute($model, $attribute);
                }
            }
            if(!$contracts && ($has || $hasCheckedContracts))
                $this->checkUnique($model, $attributes);
        }

    }

    private function hasOperatorContract($model)
    {
        return ClientContract::find()
            ->andWhere(['contragent_id' => $model->id])
            ->andWhere(['=', 'contract_type_id', ContractType::OPERATOR])
            ->count() ? true : false;
    }

    private function hasCheckedContract($model)
    {
        return ClientContract::find()
            ->andWhere(['contragent_id' => $model->id])
            ->andWhere(['!=', 'state', 'unchecked'])
            ->count() ? true : false;
    }

    protected function checkUnique($model, $attributes)
    {
        $query = $model::find();

        $labels = [];
        foreach ($attributes as $attribute) {
            $labels[] = $model->getAttributeLabel($attribute);
            $query->andWhere([$attribute => $model->$attribute]);
        }
        $query->andWhere(['!=', 'id', $model->id]);
        $models = $query->all();

        if ($models) {
            foreach ($attributes as $attribute)
                $this->addError($model, $attribute, '{attrs} must be unique', ['attrs' => implode(', ', $labels)]);
            //$this->addError($model, $attribute, 'Связка {attrs} должна быть уникальной', ['attrs' => implode(', ', $labels)]);
        }

        $double = $model::find()
            ->andWhere(['inn' => $model->inn])
            ->andWhere(['!=', 'super_id', $model->super_id])
            ->one();
        if ($double)
            $this->addError($model, 'inn', 'Inn is already in another client <a href="/contragent/edit?id={contragentId}" target="_blank">контрагента</a>', ['contragentId' => $double->id]);
    }
}