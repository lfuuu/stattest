<?php
namespace app\classes\validators;

use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Country;
use yii\validators\Validator;

class InnKppValidator extends Validator
{

    protected $attrValidator = [];

    /**
     * Init
     */
    public function init()
    {
        parent::init();
        $this->attrValidator = [
            'inn' => InnValidator::className(),
            'kpp' => KppValidator::className(),
        ];
    }

    /**
     * @param ClientContragent $model
     * @param null $attributes
     */
    public function validateAttributes($model, $attributes = null)
    {
        $attributes = [];
        if (in_array($model->legal_type, ['ip', 'legal'])) {
            $attributes[] = 'inn';
        }

        if ($model->legal_type == 'legal' && $model->country_id == Country::RUSSIA) {
            $attributes[] = 'kpp';
        }

        $hasCheckedContracts = $model->hasChecked || $this->_hasCheckedContract($model);

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
                $this->_checkUnique($model, $attributes);
            }
        }
    }

    /**
     * Есть ли проверенные договора у контрагента
     *
     * @param ClientContragent $model
     * @return bool
     */
    private function _hasCheckedContract($model)
    {
        return (bool) ClientContract::find()
            ->andWhere(['contragent_id' => $model->id])
            ->andWhere(['!=', 'state', ClientContract::STATE_UNCHECKED])
            ->count();
    }

    /**
     * Проверка на уникальность
     *
     * @param ClientContragent $model
     * @param array $attributes
     */
    private function _checkUnique($model, $attributes)
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
            $errorStr = '{attrs} должен быть уникальный (контрагент #{contragentId}, {contragentName}, ЛС: {accountId})';
            if ($model->isSimpleValidation) {
                $errorStr = 'Компания с данным ИНН и КПП уже зарегистриорована';
            }

            foreach ($attributes as $attribute) {
                $this->addError($model, $attribute,
                    $errorStr, [
                    'attrs' => implode(', ', $labels),
                    'contragentId' => $notUniqueContragent->id,
                    'contragentName' => $notUniqueContragent->name,
                    'accountId' => $notUniqueContragent->getAccounts()[0]->id
                ]);
            }
        }
    }
}
