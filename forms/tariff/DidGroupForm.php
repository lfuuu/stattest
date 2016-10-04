<?php
namespace app\forms\tariff;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\models\NumberType;

class DidGroupForm extends Form
{

    public
        $id = 0,
        $country_id = 0,
        $original_country_id = 0,
        $city_id = City::DEFAULT_USER_CITY_ID,
        $name = '',
        $beauty_level = DidGroup::BEAUTY_LEVEL_STANDART,
        $number_type_id = NumberType::ID_GEO_DID
    ;

    /** @var DidGroup */
    public $didGroup = null;

    public function rules()
    {
        return [
            ['country_id', 'safe'],
            [['country_id', 'city_id', 'name', 'beauty_level', 'number_type_id'], 'required', 'on' => 'save'],
            [['name'], FormFieldValidator::className()],
            ['country_id', 'in', 'range' => array_keys(Country::getList()), 'on' => 'save'],
            ['city_id', 'validateCity', 'on' => 'save'],
            ['beauty_level', 'in', 'range' => array_keys(DidGroup::$beautyLevelNames), 'on' => 'save'],
            ['number_type_id', 'in', 'range' => array_keys(NumberType::getList()), 'on' => 'save'],
            ['number_type_id', 'validateNumberTypeDependency'],
        ];
    }

    public function attributeLabels()
    {
        return (new DidGroup)->attributeLabels() + [
            'country_id' => 'Страна',
        ];
    }

    public function validateCity()
    {
        if (!array_key_exists($this->city_id, City::dao()->getList(false, $this->country_id))) {
            $this->addError('city_id', 'Значение "Город" неверно');
        }
    }

    public function validateNumberTypeDependency()
    {
        if ($this->didGroup && $this->didGroup->number_type_id != $this->number_type_id) {
            if (Number::findOne(['did_group_id' => $this->didGroup->id])) {
                $this->addError('number_type_id', 'Невозможно сменить тип номер, т.к. у этой DID-групы есть номера');
            }
        }
    }

    /**
     * Инициализация модели формы.
     *
     * @param DidGroup $didGroup
     */
    public function initModel(DidGroup $didGroup)
    {
        $this->didGroup = $didGroup;
        $this->setAttributes($didGroup->getAttributes(), false);
    }

    /**
     * Инициализация данных формы на основе загруженных значений
     *
     * @return bool
     */
    public function initForm()
    {
        $city = City::findOne(['id' => $this->city_id]);

        //country change
        if ($this->country_id && $city->country_id != $this->country_id) {
            $this->city_id = array_keys(City::dao()->getList(false, $this->country_id))[0];
            $city = City::findOne(['id' => $this->city_id]);
        }

        //first load
        if (!$this->country_id && $city) {
            $this->country_id = $city->country_id;
        }

        //for breadcrumbs
        if (!$this->original_country_id && $this->didGroup && $this->didGroup->city_id) {
            if (!$city || $city->id != $this->didGroup->city_id) {
                $city = City::findOne(['id' => $this->didGroup->city_id]);
            }
            $this->original_country_id = $city->country_id;
        }

        return true;
    }

    /**
     * Действие. Сохранение формы.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->didGroup) {
            $this->didGroup = new DidGroup();
        }

        $this->didGroup->setAttributes($this->getAttributes());

        $result = $this->didGroup->save();

        if ($result) {
            $this->id = $this->didGroup->id;
        }

        return $result;
    }
}