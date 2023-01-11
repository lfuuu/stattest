<?php

namespace app\modules\sim\forms\registry;

use app\modules\sim\models\CardType;
use app\modules\sim\models\Registry;
use app\exceptions\ModelValidationException;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\models\RegionSettings;
use yii\base\InvalidArgumentException;

class Form extends \app\classes\Form
{
    public
        $id = 0,
        $region_sim_settings_id,
        $iccid_from,
        $iccid_to,
        $imsi_from,
        $imsi_to,
        $imsi_s1_from,
        $imsi_s1_to,
        $imsi_s2_from,
        $imsi_s2_to,
        $sim_type_id = CardType::ID_DEFAULT
    ;

    /** @var Registry */
    public $registry = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->id) {
            if (!( $this->registry = Registry::findOne(['id' => $this->id]))) {
                throw new InvalidArgumentException('Неверная заливка');
            }
        } else {
            $this->region_sim_settings_id = RegionSettings::getDefaultId();
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['region_sim_settings_id', 'iccid_from', 'iccid_to', 'imsi_from', 'imsi_to', 'sim_type_id'], 'required'],
            [['id', 'region_sim_settings_id', 'sim_type_id'], 'integer'],
            [['iccid_from', 'iccid_to'], 'string', 'max' => 16],
            [['imsi_from', 'imsi_to'], 'string', 'max' => 16],
            [['imsi_s1_from', 'imsi_s1_to'], 'string', 'min' => 15, 'max' => 15],
            [['imsi_s2_from', 'imsi_s2_to'], 'string', 'min' => 15, 'max' => 15],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'region_sim_settings_id' => 'Настройки региона',

            'iccid_from' => 'ICCID с',
            'iccid_to' => 'ICCID по',

            'imsi_from' => 'IMSI с',
            'imsi_to' => 'IMSI по',

            'imsi_s1_from' => 'IMSI S1 с (15 символов)',
            'imsi_s1_to' => 'IMSI S1 по (15 символов)',

            'imsi_s2_from' => 'IMSI S2 с (15 символов)',
            'imsi_s2_to' => 'IMSI S2 по (15 символов)',

            'sim_type_id' => 'Тип SIM-карты',
        ];
    }

    /**
     * Попробовать сохранить, если POST
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryToSave()
    {
        $result = false;

        $this->registry = Registry::findOne(['id' => $this->id]);

        $post = \Yii::$app->request->post();
        if (!empty($post['save'])) {
            if (!$this->registry) {
                $this->registry = new Registry();

                $this->registry->state = RegistryState::NEW;
            }

            $this->load($post);

            // set fields
            if ($regionSettings = RegionSettings::findOne(['id' => $this->region_sim_settings_id])) {
                /** @var RegionSettings $regionSettings */
                $this->registry->region_sim_settings_id = $regionSettings->id;
            }

            foreach ([
                         'iccid_from',
                         'iccid_to',
                         'imsi_from',
                         'imsi_to',
                         'imsi_s1_from',
                         'imsi_s1_to',
                         'imsi_s2_from',
                         'imsi_s2_to',
                         'sim_type_id',
                     ] as $field) {
                $this->registry->{$field} = $this->{$field};
            }

            $this->registry->count = (int)$this->iccid_to - (int)$this->iccid_from + 1;

            // saving
            $result = $this->registry->validate();
            $result = $result && $this->registry->validateRages();
            $result = $result && $this->registry->save();
            if (!$result) {
                throw new ModelValidationException($this->registry);
            }

            if ($result) {
                $this->id = $this->registry->id;
            }
        }

        return $result;
    }

    /**
     * Изменение статуса с проверкой
     *
     * @param int $id
     * @param int $stateFrom
     * @param int $stateTo
     * @param string $errorMessage
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected static function changeState($id, $stateFrom, $stateTo, $errorMessage)
    {
        $model = Registry::findOne(['id' => $id]);
        if (!$model) {
            throw new InvalidArgumentException('Неверная заливка');
        }

        /** @var Registry $model */
        if ($model->state != $stateFrom) {
            $errorMessage = strtr($errorMessage, ['{state}' => $model->stateName]);

            throw new InvalidArgumentException($errorMessage);
        }

        $model->state = $stateTo;
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        return $id;
    }

    /**
     * Start
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function start($id)
    {
        return self::changeState(
            $id,
            RegistryState::NEW,
            RegistryState::STARTED,
            'Заливка в статусе {state} не может быть запущена.'
        );
    }

    /**
     * Cancel
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function cancel($id)
    {
        return self::changeState(
            $id,
            RegistryState::NEW,
            RegistryState::CANCELLED,
            'Заливка в статусе {state} не может быть отменена.'
        );
    }

    /**
     * Restore
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function restore($id)
    {
        return self::changeState(
            $id,
            RegistryState::CANCELLED,
            RegistryState::NEW,
            'Заливка в статусе {state} не может быть восстановлена.'
        );
    }
}