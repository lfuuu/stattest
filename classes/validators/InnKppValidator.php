<?php
namespace app\classes\validators;

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
    public function validateAttributes(ClientContragent $model, $attributes = null)
    {
        $attributes = [];
        if (in_array($model->legal_type, ['ip', 'legal'])) {
            $attributes[] = 'inn';
        }

        if ($model->legal_type == 'legal' && $model->country_id == Country::RUSSIA) {
            $attributes[] = 'kpp';
        }

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

            if ($isValidated) {
                $this->_checkUnique($model, $attributes);
            }
        }
    }

    /**
     * Проверка на уникальность
     *
     * @param ClientContragent $model
     * @param array $attributes
     */
    private function _checkUnique(ClientContragent $model, $attributes)
    {
        $query = $model::find();

        $labels = [];

        foreach ($attributes as $attribute) {
            $labels[] = $model->getAttributeLabel($attribute);
        }

        if (!$model->isNewRecord) {
            $query->andWhere(['!=', 'id', $model->id]);
        }

        $isNotUniqueContragent = false;

        if (in_array('inn', $attributes)) {
            $query->andWhere(['inn' => $model->inn]);
            /** @var ClientContragent $contragent */
            foreach ($query->each() as $contragent) {
                $isNotUniqueContragent = $contragent;

                // допускается один ИНН, и разные КПП в рамках одного (супер)клиента
                if (
                    in_array('kpp', $attributes) &&
                    $model->kpp &&
                    $model->super_id == $contragent->super_id &&
                    $model->kpp != $contragent->kpp
                ) {
                    $isNotUniqueContragent = false;
                }

                if ($isNotUniqueContragent) {
                    break;
                }
            }
        }

        if ($isNotUniqueContragent) {
            $errorStr = '{attrs} должен быть уникальный (контрагент #{contragentId}, {contragentName}, ЛС: {accountId})';
            if ($model->isSimpleValidation) {
                $errorStr = 'Компания с данным ИНН и КПП уже зарегистриорована';
            }

            foreach ($attributes as $attribute) {
                $this->addError($model, $attribute,
                    $errorStr, [
                    'attrs' => implode(', ', $labels),
                    'contragentId' => $isNotUniqueContragent->id,
                    'contragentName' => $isNotUniqueContragent->name,
                    'accountId' => $isNotUniqueContragent->getAccounts()[0]->id
                ]);
            }
        }
    }
}
