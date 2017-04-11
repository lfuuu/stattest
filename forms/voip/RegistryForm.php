<?php
namespace app\forms\voip;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\models\City;
use app\models\Country;
use app\models\NumberType;
use app\models\voip\Registry;
use app\modules\nnp\models\NumberRange;

class RegistryForm extends Form
{

    public
        $id = 0,
        $country_id = Country::RUSSIA,
        $city_id = City::DEFAULT_USER_CITY_ID,
        $city_number_format = '',
        $city_number_format_length = 0,
        $source = VoipRegistrySourceEnum::OPERATOR,
        $number_type_id = NumberType::ID_GEO_DID,
        $number_from,
        $number_to,
        $account_id,
        $comment = '',
        $ndc = NumberRange::DEFAULT_MOSCOW_NDC,
        $ndsList = []
    ;

    /** @var Registry  */
    public $registry = null;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['country_id', 'city_id', 'source', 'number_type_id', 'number_from', 'number_to', 'account_id','ndc'],
                'required',
                'on' => 'save'
            ],
            [
                ['country_id', 'city_id', 'source', 'number_type_id', 'number_from', 'number_to', 'account_id', 'comment'],
                FormFieldValidator::className()
            ],
            ['country_id', 'in', 'range' => array_keys(Country::getList()), 'on' => 'save'],
            ['city_id', 'validateCity', 'on' => 'save'],
            ['source', 'in', 'range' => array_keys(VoipRegistrySourceEnum::$names), 'on' => 'save'],
            ['number_type_id', 'in', 'range' => array_keys(NumberType::getList()), 'on' => 'save'],
            ['account_id', AccountIdValidator::className(), 'on' => 'save'],
            [['number_from', 'number_to', 'account_id'], 'required', 'on' => 'save'],
            ['account_id', 'integer', 'on' => 'save'],
            ['number_from', 'validateNumbersRange'],
            ['ndc', 'safe']
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
            'ndc' => 'NDC'
        ];
    }

    /**
     * Валидатор города
     */
    public function validateCity()
    {
        if (!array_key_exists(
            $this->city_id,
            City::getList(
                $isWithEmpty = false,
                $this->country_id,
                $isWithNullAndNotNull = false,
                $isUsedOnly = false)
        )) {
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
            $this->addError('number_from', 'Слишком большой диапазон номеров.');
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
     * @param bool $isFromPost
     * @return bool
     */
    public function initForm($isFromPost = false)
    {
        if ($this->country_id && $this->city_id) {
            /** @var City $city */
            $city = City::findOne(['id' => $this->city_id]);
            if ($city && $city->country_id != $this->country_id) {
                $cities = City::getList($isWithEmpty = false, $this->country_id, $isWithNullAndNotNull = false, $isUsedOnly = false);
                $this->city_id = array_keys($cities)[0];
            }
        }

        $this->_checkNDC();
        $this->_setCityNumberFormat();

        if ($isFromPost) {
            $this->_prepareNumbesFromPost();
        }

        return true;
    }

    /**
     * Подготовка номеров из POST-данных
     */
    private function _prepareNumbesFromPost()
    {
        $this->number_from = substr($this->number_from, $this->city_number_format_length);
        $this->number_to = substr($this->number_to, $this->city_number_format_length);
    }

    /**
     * Проверка наличия NDC для данного города и страны
     */
    private function _checkNDC()
    {
        $this->ndsList = NumberRange::getNDCList($this->country_id, $this->city_id);

        if (!isset($this->ndsList[$this->ndc])) {
            $this->ndc = reset($this->ndsList);
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

        $this->city_number_format = str_replace(["9", "0"], ["\\9", "\\0"],
                $country->prefix . " " . $this->ndc) . " 999999[9][9][9]";

        $this->city_number_format_length = strlen($country->prefix . " " . $this->ndc) + 1;
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

        foreach ([
                     'country_id',
                     'city_id',
                     'source',
                     'number_type_id',
                     'number_from',
                     'number_to',
                     'account_id',
                     'comment',
                     'ndc'
                 ] as $field) {
            $this->registry->{$field} = $this->{$field};
        }

        $countryPrefix = $this->registry->country->prefix;

        $this->registry->number_full_from = $countryPrefix . $this->ndc . $this->number_from;
        $this->registry->number_full_to = $countryPrefix . $this->ndc . $this->number_to;

        if (!($result = $this->registry->save())) {
            throw new ModelValidationException($this->registry);
        }

        if ($result) {
            $this->id = $this->registry->id;
        }

        return $result;
    }
}