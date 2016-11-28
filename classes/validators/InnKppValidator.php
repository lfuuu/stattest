<?php
namespace app\classes\validators;

use app\models\ClientContract;
use app\models\Business;
use app\models\ClientContragent;
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

    public function validateAttributes($model, $attributes = null)
    {
        $attributes = [];
        if (in_array($model->legal_type, ['ip', 'legal'])) {
            $attributes[] = 'inn';
        }

        if ($model->legal_type == 'legal' && $model->country_id == Country::RUSSIA) {
            $attributes[] = 'kpp';
        }

        $hasCheckedContracts = $model->hasChecked || $this->hasCheckedContract($model);

        if ($attributes) {
            $isValidated = false;
            foreach ($attributes as $attribute) {
                if ($model->$attribute && ($this->when === null || call_user_func($this->when, $model, $attribute))
                ) {
                    $isValidated = true;
                    self::createValidator($this->attrValidator[$attribute], $model,
                        $attribute)->validateAttribute($model, $attribute);
                }
            }
            if ($isValidated && $hasCheckedContracts) {
                $this->checkUnique($model, $attributes);
            }
        }
    }

    private function hasCheckedContract($model)
    {
        return (bool) ClientContract::find()
            ->andWhere(['contragent_id' => $model->id])
            ->andWhere(['!=', 'state', ClientContract::STATE_UNCHECKED])
            ->count();
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
        /** @var ClientContragent $notUniqueContragent */
        $notUniqueContragent = $query->one();

        if ($notUniqueContragent) {
            foreach ($attributes as $attribute) {
                $this->addError($model, $attribute, '{attrs} должен быть уникальный (контрагент #{contragentId}, {contragentName}, ЛС: {accountId})', [
                    'attrs' => implode(', ', $labels),
                    'contragentId' => $notUniqueContragent->id,
                    'contragentName' => $notUniqueContragent->name,
                    'accountId' => $notUniqueContragent->getAccounts()[0]->id
                ]);
            }
        }
    }
}
