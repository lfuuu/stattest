<?php

namespace app\modules\sim\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\User;
use app\modules\sim\classes\RegistryState;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $state
 * @property int $region_sim_settings_id
 * @property int $count
 * @property string $iccid_from
 * @property string $iccid_to
 * @property string $imsi_prefix
 * @property string $imsi_from
 * @property string $imsi_to
 * @property string $imsi_s1_from
 * @property string $imsi_s1_to
 * @property string $imsi_s2_from
 * @property string $imsi_s2_to
 *
 * @property string $log
 * @property string $errors
 *
 * @property string $created_at
 * @property string $updated_at
 * @property string $started_at
 * @property string $completed_at
 * @property integer $created_by
 *
 * @property-read RegionSettings $regionSettings
 * @property-read RegionSettings $actualSettings
 * @property-read User $createdBy
 * @property-read CardType $type
 */
class Registry extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'sim_registry';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['state', 'region_sim_settings_id', 'iccid_from', 'iccid_to', 'imsi_from', 'imsi_to', 'count', 'sim_type_id'], 'required'],
            [['id', 'region_sim_settings_id', 'state', 'count', 'created_by', 'sim_type_id'], 'integer'],
            [['iccid_from', 'iccid_to'], 'string', 'max' => 16],
            [['imsi_from', 'imsi_to'], 'string', 'max' => 16],
            [['imsi_s1_from', 'imsi_s1_to'], 'string', 'min' => 15, 'max' => 15],
            [['imsi_s2_from', 'imsi_s2_to'], 'string', 'min' => 15, 'max' => 15],
            [['log', 'errors'], 'string'],
            [['created_at', 'updated_at', 'started_at', 'completed_at'], 'safe'],
            //[['updated_at', 'started_at', 'completed_at'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['sim_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => CardType::class, 'targetAttribute' => ['sim_type_id' => 'id']],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',

            'state' => 'Статус',
            'region_sim_settings_id' => 'Настройки региона',

            'iccid_from' => 'ICCID с',
            'iccid_to' => 'ICCID по',

            'imsi_from' => 'IMSI с',
            'imsi_to' => 'IMSI по',

            'imsi_s1_from' => 'IMSI S1 с',
            'imsi_s1_to' => 'IMSI S1 по',

            'imsi_s2_from' => 'IMSI S2 с',
            'imsi_s2_to' => 'IMSI S2 по',

            'count' => 'Количество',

            'log' => 'Лог',
            'errors' => 'Ошибки',

            'created_at' => 'Создан',
            'updated_at' => 'Изменён',

            'started_at' => 'Запущен',
            'completed_at' => 'Завершён',

            'created_by' => 'Кем создан',
            'sim_type_id' => 'Тип карты',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто создал" и "кто обновил"
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'value' => \Yii::$app->user->getId(),
            ],
        ];
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $validation = parent::validate($attributeNames, $clearErrors);

        if ($validation) {
            $result = true;

            // check ICCID
            $result = $result && $this->validateICCID();

            // check IMSI
            $result = $result && $this->validateIMSI();

            // check IMSI_S1
            $result = $result && $this->validateIMSIS1();

            // check IMSI_S2
            $result = $result && $this->validateIMSIS2();

            // check count
            $result = $result && $this->validateCount();

            return $result;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function validateICCID()
    {
        if ($this->iccid_from > $this->iccid_to) {
            $errorText = sprintf(
                'Некорректный диапазон ICCID (%s-%s)',
                $this->iccid_from,
                $this->iccid_to,
            );
            $this->addError('iccid_to', $errorText);

            return false;
        }

        $settings = $this->actualSettings;
        $maxICCID = intval(str_repeat('9', $settings->iccid_range_length));
        if ($this->iccid_from > $maxICCID) {
            $errorText = sprintf(
                'Значение ICCID больше допустимого (%s > %s)',
                $this->iccid_from,
                $maxICCID,
            );
            $this->addError('iccid_from', $errorText);

            return false;
        }

        if ($this->iccid_from <= $settings->iccid_last_used) {
            $errorText = sprintf(
                'Последний использованный ICCID (%s) по региону %s больше либо равен указанному в заливке (%s)',
                $settings->iccid_last_used,
                $this->regionSettings->getRegionFullName(),
                $this->iccid_from
            );
            $this->addError('iccid_from', $errorText);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateIMSI()
    {
        if ($this->imsi_from > $this->imsi_to) {
            $errorText = sprintf(
                'Некорректный диапазон IMSI (%s-%s)',
                $this->imsi_from,
                $this->imsi_to,
            );
            $this->addError('imsi_to', $errorText);

            return false;
        }

        $settings = $this->actualSettings;
        $maxIMSI = intval(str_repeat('9', $settings->imsi_range_length));
        if ($this->imsi_from > $maxIMSI) {
            $errorText = sprintf(
                'Значение IMSI больше допустимого (%s > %s)',
                $this->imsi_from,
                $maxIMSI,
            );
            $this->addError('imsi_from', $errorText);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateIMSIS1()
    {
        if ($this->imsi_s1_from xor $this->imsi_s1_to) {
            $errorText = sprintf(
                'Неполный диапазон IMSI_S1 (%s-%s)',
                $this->imsi_s1_from,
                $this->imsi_s1_to,
            );
            $this->addError('imsi_s1_to', $errorText);
        } elseif (!$this->imsi_s1_from) {
            // both empty
            return true;
        }

        if ($this->imsi_s1_from > $this->imsi_s1_to) {
            $errorText = sprintf(
                'Некорректный диапазон IMSI_S1 (%s-%s)',
                $this->imsi_s1_from,
                $this->imsi_s1_to,
            );
            $this->addError('imsi_s1_to', $errorText);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateIMSIS2()
    {
        if ($this->imsi_s2_from > $this->imsi_s2_to) {
            $errorText = sprintf(
                'Некорректный диапазон IMSI_S2 (%s-%s)',
                $this->imsi_s2_from,
                $this->imsi_s2_to,
            );
            $this->addError('imsi_s2_to', $errorText);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateCount()
    {
        $count = intval($this->iccid_to) - intval($this->iccid_from);

        if ($this->imsi_to - $this->imsi_from != $count) {
            $errorText = sprintf(
                'Некорректная длина диапазона IMSI (%s-%s) не соответствует длине %s %s',
                $this->imsi_from,
                $this->imsi_to,
                $this->imsi_to - $this->imsi_from,
                $count + 1
            );
            $this->addError('imsi_to', $errorText);

            return false;
        }

        if ($this->imsi_s1_to) {
            if ($this->imsi_s1_to - $this->imsi_s1_from != $count) {
                $errorText = sprintf(
                    'Некорректная длина диапазона IMSI_S1 (%s-%s) не соответствует длине %s',
                    $this->imsi_s1_from,
                    $this->imsi_s1_to,
                    $count + 1
                );
                $this->addError('imsi_s1_to', $errorText);

                return false;
            }
        }

        if ($this->imsi_s2_to) {
            if ($this->imsi_s2_to - $this->imsi_s2_from != $count) {
                $errorText = sprintf(
                    'Некорректная длина диапазона IMSI_S2 (%s-%s) не соответствует длине %s',
                    $this->imsi_s2_from,
                    $this->imsi_s2_to,
                    $count + 1
                );
                $this->addError('imsi_s2_to', $errorText);

                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateRages()
    {
        $from = $this->getICCIDFromFull();
        $to = $this->getICCIDToFull();
        $count = Imsi::find()
            ->where(['>=', 'iccid', $from])
            ->andWhere(['<=', 'iccid', $to])
            ->count();

        if ($count) {
            $errorText = sprintf(
                'Внутри данного диапазона ICCID (%s-%s) существуют записи, количество: %s',
                $from,
                $to,
                $count
            );
            $this->addError('iccid_to', $errorText);
        }

        $from = $this->getIMSIFromFull();
        $to = $this->getIMSIToFull();
        $count = Imsi::find()
            ->where(['>=', 'imsi', $from])
            ->andWhere(['<=', 'imsi', $to])
            ->count();

        if ($count) {
            $errorText = sprintf(
                'Внутри данного диапазона IMSI (%s-%s) существуют записи, количество: %s',
                $from,
                $to,
                $count
            );
            $this->addError('imsi_from', $errorText);
        }

        if ($this->imsi_s1_from && $this->imsi_s1_to) {
            $from = $this->imsi_s1_from;
            $to = $this->imsi_s1_to;
            $count = Imsi::find()
                ->where(['>=', 'imsi', $from])
                ->andWhere(['<=', 'imsi', $to])
                ->count();

            if ($count) {
                $errorText = sprintf(
                    'Внутри данного диапазона IMSI S1 (%s-%s) существуют записи, количество: %s',
                    $from,
                    $to,
                    $count
                );
                $this->addError('imsi_to', $errorText);

                return false;
            }
        }

        if ($this->imsi_s2_from && $this->imsi_s2_to) {
            $from = $this->imsi_s2_from;
            $to = $this->imsi_s2_to;
            $count = Imsi::find()
                ->where(['>=', 'imsi', $from])
                ->andWhere(['<=', 'imsi', $to])
                ->count();

            if ($count) {
                $errorText = sprintf(
                    'Внутри данного диапазона IMSI S2 (%s-%s) существуют записи, количество: %s',
                    $from,
                    $to,
                    $count
                );
                $this->addError('imsi_to', $errorText);
            }
        }

        return !$this->hasErrors();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegionSettings()
    {
        return $this->hasOne(RegionSettings::class, ['id' => 'region_sim_settings_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(CardType::class, ['id' => 'sim_type_id']);
    }

    /**
     * @return RegionSettings
     */
    public function getActualSettings()
    {
        return $this->regionSettings->getMainParent();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/sim/registry/view', 'id' => $this->id]);
    }

    /**
     * Добавить ошибку в лог
     *
     * @param $errorText
     */
    public function addErrorText($errorText)
    {
        $now = new \DateTime('now');
        $this->errors .=
            ($this->errors ? PHP_EOL : '') .
            sprintf('%s: %s', $now->format(DateTimeZoneHelper::DATETIME_FORMAT), $errorText);
    }

    /**
     * @return string
     */
    public function getStateName()
    {
        return RegistryState::getById($this->state);
    }

    /**
     * @return string
     */
    public function getStateClass()
    {
        switch($this->state) {
            case RegistryState::NEW:
                return 'glyphicon glyphicon-time text-info';

            case RegistryState::STARTED:
                return 'glyphicon glyphicon-download text-warning';

            case RegistryState::PROCESSING:
                return 'glyphicon glyphicon-import text-warning';

            case RegistryState::ERROR:
                return 'glyphicon glyphicon-warning-sign text-danger';

            case RegistryState::COMPLETED:
                return 'glyphicon glyphicon-saved text-success';
        }

        return '';
    }

    /// ******************** Cuts
    /**
     * @return string
     */
    public function getICCIDFromCut()
    {
        $settings = $this->actualSettings;
        return sprintf('%0' . $settings->iccid_range_length . 'd', $this->iccid_from);
    }

    /**
     * @return string
     */
    public function getICCIDToCut()
    {
        $settings = $this->actualSettings;
        return sprintf('%0' . $settings->iccid_range_length . 'd', $this->iccid_to);
    }

    /**
     * @return string
     */
    public function getIMSIFromCut()
    {
        $settings = $this->actualSettings;
        return sprintf('%0' . $settings->imsi_range_length . 'd', $this->imsi_from);
    }

    /**
     * @return string
     */
    public function getIMSIToCut()
    {
        $settings = $this->actualSettings;
        return sprintf('%0' . $settings->imsi_range_length . 'd', $this->imsi_to);
    }

    //// ******************** Full
    /**
     * @return string
     */
    public function getICCIDFromFull()
    {
        $settings = $this->actualSettings;

        return sprintf('%s%s%s%s', $settings->iccid_prefix, $settings->iccid_region_code, $settings->iccid_vendor_code, $this->getICCIDFromCut());
    }

    /**
     * @return string
     */
    public function getICCIDToFull()
    {
        $settings = $this->actualSettings;

        return sprintf('%s%s%s%s', $settings->iccid_prefix, $settings->iccid_region_code, $settings->iccid_vendor_code, $this->getICCIDToCut());
    }

    /**
     * @return string
     */
    public function getIMSIFromFull()
    {
        $settings = $this->actualSettings;

        return sprintf('%s%s%s', $settings->imsi_prefix, $settings->imsi_region_code, $this->getIMSIFromCut());
    }

    /**
     * @return string
     */
    public function getIMSIToFull()
    {
        $settings = $this->actualSettings;

        return sprintf('%s%s%s', $settings->imsi_prefix, $settings->imsi_region_code, $this->getIMSIToCut());
    }

    public function createLog()
    {
        $log = '';

        $log .= 'ICCID: ';
        $log .= $this->getICCIDFromFull();
        $log .= '-';
        $log .= $this->getICCIDToFull();
        $log .= PHP_EOL;

        $log .= 'IMSI: ';
        $log .= $this->getIMSIFromFull();
        $log .= '-';
        $log .= $this->getIMSIToFull();
        $log .= PHP_EOL;

        $this->log = $log;
    }


}