<?php

namespace app\forms\voip;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\models\City;
use app\models\Country;
use app\models\voip\Registry;
use app\models\voip\Source;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;

class RegistryForm extends Form
{

    public
        $id = 0,
        $country_id = Country::RUSSIA,
        $city_id = City::DEFAULT_USER_CITY_ID,
        $city_number_format = '',
        $city_number_format_length = 0,
        $source = VoipRegistrySourceEnum::OPERATOR,
        $ndc_type_id = NdcType::ID_GEOGRAPHIC,
        $solution_number,
        $numbers_count,
        $solution_date,
        $number_from,
        $number_to,
        $account_id,
        $comment = '',
        $ndc = NumberRange::DEFAULT_MOSCOW_NDC,
        $ndcList = [],
        $fmc_trunk_id = null,
        $mvno_partner_id = null,
        $nnp_operator_id = null,
        $nnp_operator_name = ''
    ;


    /** @var Registry */
    public $registry = null;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['country_id', 'source', 'ndc_type_id', 'number_from', 'number_to', 'account_id', 'ndc',],
                'required',
                'on' => 'save'
            ],
            [
                'city_id',
                'required',
                'on' => 'save',
                'when' => function ($model) {
                    return $model->ndc_type_id == NdcType::ID_GEOGRAPHIC;
                }
            ],
            [
                ['country_id', 'city_id', 'source', 'ndc_type_id', 'number_from', 'number_to', 'account_id', 'comment', 'solution_number', 'numbers_count', 'solution_date'],
                FormFieldValidator::class
            ],
            ['country_id', 'in', 'range' => array_keys(Country::getList()), 'on' => 'save'],
            ['city_id', 'validateCity', 'on' => 'save'],
            ['source', 'in', 'range' => array_keys(Source::getList()), 'on' => 'save'],
            ['ndc_type_id', 'in', 'range' => array_keys(NdcType::getList()), 'on' => 'save'],
            ['account_id', AccountIdValidator::class, 'on' => 'save'],
            [['number_from', 'number_to', 'account_id'], 'required', 'on' => 'save'],
            ['account_id', 'integer', 'on' => 'save'],
            ['number_from', 'validateNumbersRange'],
            [['ndc', 'mvno_partner_id', 'fmc_trunk_id', 'number_from', 'number_to'], 'safe'],
            [['nnp_operator_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return (new Registry)->attributeLabels() + [
                'comment' => 'Комментарий',
                'city_number_format' => 'Формат номера',
                'ndc' => 'NDC',
                'nnp_operator_name' => 'ННП-оператор',
            ];
    }

    /**
     * Валидатор города
     */
    public function validateCity()
    {
        if (!NdcType::isCityDependent($this->ndc_type_id)) {
            return;
        }

        if (!array_key_exists(
            $this->city_id,
            City::getList(
                $isWithEmpty = false,
                $this->country_id,
                $isWithNullAndNotNull = false,
                $isUsedOnly = false)
        )
        ) {
            $this->addError('city_id', 'Значение "Город" неверно');
        }
    }

    /**
     * Валидатор номерного диапазона
     */
    public function validateNumbersRange()
    {
        if ($this->number_from > $this->number_to) {
            $this->addError('number_from', 'Номер "c" меньше номера "по"');
        } elseif (($this->number_to - $this->number_from) > 100000) {
            $this->addError('number_from', 'Слишком большой диапазон номеров. (> 100000)' );
        }
    }

    /**
     * Инициализация модели формы.
     *
     * @param Registry $registry
     */
    public function initModel(Registry $registry)
    {
        $this->registry = $registry;
        $this->setAttributes($registry->getAttributes(), false);
    }

    /**
     * Инициализация данных формы на основе загруженных значений
     *
     * @param bool $isSaveFromPost
     * @return bool
     * @throws \LogicException
     */
    public function initForm($isSaveFromPost = false)
    {
        if ($this->country_id && $this->city_id) {
            /** @var City $city */
            $city = City::findOne(['id' => $this->city_id]);
            if ($city && $city->country_id != $this->country_id) {
                $cities = City::getList($isWithEmpty = false, $this->country_id, $isWithNullAndNotNull = false, $isUsedOnly = false);
                $this->city_id = array_keys($cities)[0];
            }
        }

        $this->_setNDC();
        $this->_setCityNumberFormat();
        $this->_setNnpOperator();

        if ($isSaveFromPost) {
            $this->_prepareNumbesFromPost();
        } elseif ($this->registry) {
            $this->number_from = $this->registry->number_from;
            $this->number_to = $this->registry->number_to;
        }


        return true;
    }

    /**
     * Подготовка номеров из POST-данных
     */
    private function _prepareNumbesFromPost()
    {
        $this->number_from = str_replace('_', '', substr($this->number_from, $this->city_number_format_length));
        $this->number_to = str_replace('_', '', substr($this->number_to, $this->city_number_format_length));
    }

    /**
     * Проверка наличия NDC для данного города и страны
     */
    private function _setNDC()
    {
        if (!NdcType::isCityDependent($this->ndc_type_id)) {
            $this->city_id = null;
        }

        $this->ndcList = NumberRange::getNdcList(
            $this->country_id,
            null, // $this->ndc_type_id == NdcType::ID_MOBILE ? null : $this->city_id,
            $this->ndc_type_id
        );

        if (!isset($this->ndcList[$this->ndc])) {
            $this->ndc = $this->ndcList ? reset($this->ndcList) : '';
        }
    }

    /**
     * Установка формата вводимого номера
     */
    private function _setCityNumberFormat()
    {
        $country = Country::findOne(['code' => $this->country_id]);
        if (!$country) {
            return;
        }

        $this->city_number_format = str_replace(['9', '0'], ['\\9', '\\0'],
                $country->prefix . ' ' . $this->ndc) . ' 999999[9][9][9]';

        $this->city_number_format_length = strlen($country->prefix . ' ' . $this->ndc) + 1;
    }

    private function _setNnpOperator()
    {
        if (!$this->nnp_operator_id) {
            return ;
        }

        $this->nnp_operator_name = Operator::find()->where(['id' => $this->nnp_operator_id])->select('name')->scalar();
    }

    /**
     * Действие. Сохранение формы.
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function save()
    {
        if (!$this->registry) {
            $this->registry = new Registry();
        }

        if (!NdcType::isCityDependent($this->ndc_type_id)) {
            $this->city_id = null;
        }

        foreach ([
                     'country_id',
                     'city_id',
                     'source',
                     'ndc_type_id',
                     'number_from',
                     'number_to',
                     'account_id',
                     'comment',
                     'solution_date',
                     'solution_number',
                     'ndc',
                     'fmc_trunk_id',
                     'nnp_operator_id',
                     'mvno_partner_id',
                 ] as $field) {
            $this->registry->{$field} = $this->{$field};
        }

        $countryPrefix = $this->registry->country->prefix;

        $this->registry->number_full_from = $countryPrefix . $this->ndc . $this->number_from;
        $this->registry->number_full_to = $countryPrefix . $this->ndc . $this->number_to;
        $this->registry->numbers_count = (int)$this->number_to - (int)$this->number_from + 1;

        if (!($result = $this->registry->save())) {
            throw new ModelValidationException($this->registry);
        }

        if ($result) {
            $this->id = $this->registry->id;
        }

        return $result;
    }
}