<?php

namespace app\forms\usage;

use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\City;
use app\models\ClientAccount;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\LogTarif;
use app\models\LogUsageHistory;
use app\models\LogUsageHistoryFields;
use app\models\Number;
use app\models\TariffVoip;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use DateTime;
use DateTimeZone;
use Yii;
use yii\db\Expression;

class UsageVoipEditForm extends UsageVoipForm
{
    const MAX_COUNT_NUMBER = 100;

    /** @var ClientAccount */
    public $clientAccount;
    /** @var UsageVoip */
    public $usage;
    /** @var City */
    public $city;
    /** @var DateTimeZone */
    public $timezone;
    /** @var DateTime */
    public $today;
    /** @var DateTime */
    public $tomorrow;

    public $disconnecting_date;
    public $region;
    public $create_params = '{}';

    public
        $tariffMainStatus,
        $tariffLocalMobile,
        $tariffRussia,
        $tariffRussiaMobile,
        $tariffIntern,
        $count_numbers = 1,
        $isMultiAdd = false,
        $numbers = [];

    private static $_mapPriceToId = [
        'tariff_group_intern_price' => 'tariff_intern_id',
        'tariff_group_russia_price' => 'tariff_russia_id',
        'tariff_group_local_mob_price' => 'tariff_local_mob_id',
    ];

    /**
     * @param array $appendRules
     * @return array
     */
    public function rules($appendRules = [])
    {
        $rules = parent::rules();
        $rules[] = [['no_of_lines'], 'default', 'value' => 1];
        $rules[] = [
            [
                'client_account_id',
                'no_of_lines',
                'did',
                'tariff_main_id',
                'tariff_local_mob_id',
                'tariff_russia_id',
                'tariff_russia_mob_id',
                'tariff_intern_id',
                'ndc_type_id',
            ],
            'required',
            'on' => 'add'
        ];
        $rules[] = [
            'city_id',
            'required',
            'on' => 'add',
            'when' => function ($model) {
                return NdcType::isCityDependent($model->ndc_type_id);
            }
        ];
        $rules[] = [['did'], 'trim'];
        $rules[] = [['did'], 'validateDid', 'on' => 'add'];
        $rules[] = [['address', 'disconnecting_date'], 'string', 'on' => 'edit'];

        $rules[] = [
            [
                'no_of_lines',
                'tariff_main_id',
                'tariff_local_mob_id',
                'tariff_russia_id',
                'tariff_russia_mob_id',
                'tariff_intern_id',
            ],
            'required',
            'on' => 'change-tariff'
        ];

        $rules[] = [
            [
                'tariff_group_intern_price',
                'tariff_group_russia_price',
                'tariff_group_local_mob_price'
            ],
            'validateMinTariff'
        ];

        $rules[] = [
            ['did_group_id'],
            'required',
            'on' => 'add',
            'when' => function ($model) {
                return !in_array($model->ndc_type_id, [NdcType::ID_FREEPHONE, NdcType::ID_MCN_LINE]);
            }
        ];
        $rules[] = [
            ['line7800_id'],
            'required',
            'on' => 'add',
            'when' => function ($model) {
                return $model->ndc_type_id == NdcType::ID_FREEPHONE;
            }
        ];
        $rules[] = [
            ['line7800_id'],
            'validateNoUsedLine',
            'on' => 'add',
            'when' => function ($model) {
                return $model->ndc_type_id == NdcType::ID_FREEPHONE;
            }
        ];

        $rules[] = [
            'count_numbers',
            'integer',
            'min' => 1,
            'max' => self::MAX_COUNT_NUMBER
        ];

        return $rules;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'count_numbers' => 'Кол-во номеров (для пакетного добавления)',
            ];
    }

    /**
     * Валидация гарантированных платежей
     *
     * @param string $attribute
     */
    public function validateMinTariff($attribute)
    {
        $field = static::$_mapPriceToId[$attribute];
        $val = $this->getMinByTariff($this->{$field});
        if (($val > 0) && ($this->{$attribute} == 0)) {
            $this->addError($attribute, 'Минимальный платеж не должен быть в этом тарифе');
            return;
        }
    }

    /**
     * Валидация если "Тип" = Линия
     *
     * @param string $attribute
     */
    public function validateNoUsedLine($attribute)
    {
        if (!UsageVoip::findOne(['client' => $this->clientAccount->client, 'id' => $this->{$attribute}])) {
            $this->addError('line7800_id', 'Линия не найдена');
        }

        if (UsageVoip::findOne(['client' => $this->clientAccount->client, 'line7800_id' => $this->{$attribute}])) {
            $this->addError('line7800_id', 'Линия подключена к другому номеру');
        }
    }

    /**
     * Валидация номера
     */
    public function validateDid()
    {
        if (!$this->did) {
            return;
        }

        switch ($this->ndc_type_id) {

            case NdcType::ID_MCN_LINE: {
                if (!preg_match('/^\d{4,5}$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }

                break;
            }

            case NdcType::ID_FREEPHONE: {
                if (!preg_match('/^\d{1,2}80\d{6,8}$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }

                // no break;
            }

            default: {
                if (!preg_match('/^\d+$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }

                /** @var \app\models\Number $number */
                $number = Number::findOne($this->did);
                if ($number === null) {
                    $this->addError('did', 'Номер не найден');
                } else {
                    if (
                        $number->status != Number::STATUS_INSTOCK
                        && $number->status != Number::STATUS_NOTACTIVE_HOLD
                        && !($number->status == Number::STATUS_NOTACTIVE_RESERVED && $number->client_id == $this->clientAccount->id)
                        && !in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]) // контроль переноса номера в статусе "активный" регулируется далее
                    ) {
                        $msg = 'Номер находится в статусе "' . Number::$statusList[$number->status] . '"';

                        if ($number->status == Number::STATUS_NOTACTIVE_RESERVED || in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE])) {
                            $msg .= ', Л/С: ' . $number->client_id;
                        }

                        $this->addError('did', $msg);
                    }
                }

                if ($number && NdcType::isCityDependent($number->ndc_type_id) && $number->city_id != $this->city_id) {
                    $this->addError('did', 'Номер ' . $this->did . ' из другого города');
                }

                break;
            }
        }
    }

    /**
     * Populates the model with input data.
     *
     * @param array $data the data array to load, typically `$_POST` or `$_GET`.
     * @param string $formName the form name to use to load the data into the model.
     * If not set, [[formName()]] is used.
     * @return boolean whether `load()` found the expected form in `$data`.
     */
    public function load($data, $formName = null)
    {
        $return = parent::load($data, $formName);

        // Установка гарантированных платежей от тарифа
        if ($this->tariff_local_mob_id != $this->tariffLocalMobile) {
            if (!$this->tariff_group_local_mob_price) {
                $this->tariff_group_local_mob_price = $this->getMinByTariff($this->tariff_local_mob_id);
            }
        }

        if ($this->tariff_russia_id != $this->tariffRussia) {
            if (!$this->tariff_group_russia_price) {
                $this->tariff_group_russia_price = $this->getMinByTariff($this->tariff_russia_id);
            }
        }

        if ($this->tariff_intern_id != $this->tariffIntern) {
            if (!$this->tariff_group_intern_price) {
                $this->tariff_group_intern_price = $this->getMinByTariff($this->tariff_intern_id);
            }
        }

        return $return;
    }

    /**
     * Сохранение данных (scenario = "add")
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function add()
    {
        $tariffMain = TariffVoip::findOne($this->tariff_main_id);
        Assert::isObject($tariffMain);

        $actualFrom = $this->connecting_date;

        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $this->isMultiAdd = $this->count_numbers > 1;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            for ($i = 0; $i < $this->count_numbers; $i++) {
                if ($this->isMultiAdd) {
                    /** @var \app\models\Number $number */
                    $number = (new FreeNumberFilter)
                        ->setNdcType(NdcType::ID_GEOGRAPHIC)
                        ->setIsService(false)
                        ->setCity($this->city_id)
                        ->setCountry($this->country_id)
                        ->setDidGroup($this->did_group_id)
                        ->randomOne();

                    if (!$number) {
                        throw new \Exception(
                            'Нет свободного номера в заданной DID-групе (нашлось только: ' . $i . ')'
                        );
                    }

                    $this->did = $number->number;

                    if (!$this->validate()) {
                        $transaction->rollBack();
                        return false;
                    }
                }

                $this->numbers[] = $this->did;

                $region = $this->connection_point_id;
                if (!$region && ($country = Country::findOne(['code' => $this->country_id]))) {
                    $region = $country->default_connection_point_id;
                }

                $usage = new UsageVoip;
                $usage->region = $region;
                $usage->actual_from = $actualFrom;
                $usage->actual_to = $actualTo;
                $usage->ndc_type_id = $this->ndc_type_id;
                $usage->client = $this->clientAccount->client;
                $usage->E164 = $this->did;
                $usage->no_of_lines = (int)$this->no_of_lines;
                $usage->status = $this->status;
                $usage->address = $this->address;
                $usage->usage_comment = $this->usage_comment;
                $usage->edit_user_id = Yii::$app->user->getId();
                $usage->line7800_id = $this->ndc_type_id == NdcType::ID_FREEPHONE ? $this->line7800_id : 0;
                $usage->is_trunk = /*$this->type_id === 'operator' ? 1 : */ 0;
                $usage->one_sip = 0;
                $usage->create_params = $this->create_params;

                $this->_saveChangeHistory($usage->oldAttributes, $usage->attributes, 'usage_voip');

                $usage->save();

                $this->id = $usage->id;

                $this->_saveTariff($usage, $this->connecting_date);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Предустановка данных (scenario = "add")
     */
    public function prepareAdd()
    {
        if ($this->did) {

            /** @var Number $number */
            $number = Number::findOne(['number' => $this->did]);

            if ($number) {
                $this->ndc_type_id = $number->ndc_type_id;

                $this->connection_point_id = $number->region;
                $this->did_group_id = DidGroup::dao()->getIdByNumber($number);
                $this->city_id = $number->city_id;
                $this->country_id = $number->country_code;

            } elseif (strlen($this->did) >= 4 && strlen($this->did) <= 5) {
                $this->ndc_type_id = NdcType::ID_MCN_LINE;
            } elseif (preg_match('/^\d{1,2}80\d{6,8}$/', $this->did)) {
                $this->ndc_type_id = NdcType::ID_FREEPHONE;
            } else {
                $this->did = null;
                $this->ndc_type_id = NdcType::ID_GEOGRAPHIC;
            }

            if (!in_array($this->ndc_type_id, [NdcType::ID_GEOGRAPHIC, NdcType::ID_FREEPHONE])) {
                $this->did_group_id = null;
            }
        } else {
            if ($this->ndc_type_id == NdcType::ID_MCN_LINE) {
                $this->did = UsageVoip::dao()->getNextLineNumber();
            }
        }

        if ($this->clientAccount && !$this->connection_point_id) {
            $this->connection_point_id = $this->clientAccount->region;
        }

        if (!$this->connecting_date) {
            $this->connecting_date = date(DateTimeZoneHelper::DATE_FORMAT);
        }

        if (!$this->tariff_local_mob_id && $this->clientAccount && $this->connection_point_id) {
            $whereTariffVoip = [
                'status' => 'public',
                'connection_point_id' => $this->connection_point_id,
                'currency_id' => $this->clientAccount->currency
            ];

            $this->tariff_local_mob_id = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_LOCAL_MOBILE])->scalar();
            $this->tariff_russia_id = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_RUSSIA])->scalar();
            $this->tariff_intern_id = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_INTERNATIONAL])->scalar();
            $this->tariff_russia_mob_id = $this->tariff_russia_id;
        }
    }

    /**
     * Сохранение данных (scenario = "edit")
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function edit()
    {
        $actualFrom = $this->connecting_date;
        $activationDt = (new DateTime($actualFrom,
            $this->timezone))->setTimezone(new DateTimeZone('UTC'))->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $this->usage->actual_from = $actualFrom;
        $this->usage->activation_dt = $activationDt;
        $this->usage->status = $this->status;
        $this->usage->address = $this->address;
        $this->usage->usage_comment = $this->usage_comment;
        $this->usage->no_of_lines = (int)$this->no_of_lines;

        if (empty($this->disconnecting_date) && $this->usage->actual_to != UsageInterface::MAX_POSSIBLE_DATE) {
            $this->disconnecting_date = UsageInterface::MAX_POSSIBLE_DATE;
        }

        if ($this->usage->actual_to != $this->disconnecting_date) {
            $this->_setDisconnectionDate();
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->_saveChangeHistory($this->usage->oldAttributes, $this->usage->attributes, 'usage_voip');

            $this->usage->save();

            Number::dao()->actualizeStatusByE164($this->usage->E164);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Изменение тарифа
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function changeTariff()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->_saveTariff($this->usage, $this->tariff_change_date);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Инициализация формы
     *
     * @param ClientAccount $clientAccount
     * @param UsageVoip|null $usage
     */
    public function initModel(ClientAccount $clientAccount, UsageVoip $usage = null)
    {
        $this->clientAccount = $clientAccount;
        $this->client_account_id = $clientAccount->id;
        $this->timezone = $clientAccount->timezone;

        $this->today = new DateTime('now', $this->timezone);
        $this->today->setTime(0, 0, 0);
        $this->tomorrow = new DateTime('tomorrow', $this->timezone);
        $this->tomorrow->setTime(0, 0, 0);

        if (!$this->country_id) {
            $this->country_id = $this->clientAccount->country_id;
        }

        // Если услуга известна (scenario = edit)
        if ($usage) {
            $this->usage = $usage;
            $this->id = $usage->id;
            $this->connection_point_id = $usage->region;
            $this->connecting_date = $usage->actual_from;
            $this->disconnecting_date = (new DateTime($usage->actual_to))->format('Y') === '4000' ? '' : $usage->actual_to;
            $this->tariff_change_date = $this->today->format(DateTimeZoneHelper::DATE_FORMAT);

            $this->setAttributes($usage->getAttributes(), false);
            $this->did = $usage->E164;
            $this->ndc_type_id = $usage->ndc_type_id;
            if ($usage->line7800 !== null) {
                $this->line7800_id = $usage->line7800->E164;
            }

            // Включенный на услуге тариф
            $currentTariff = $usage->logTariff;

            // "Тариф Основной" от включенного тарифа
            $this->tariff_main_id = $currentTariff->id_tarif;
            // "Тариф Местные мобильные" от включенного тарифа
            $this->tariff_local_mob_id = $this->tariffLocalMobile = $currentTariff->id_tarif_local_mob;
            // "Тариф Россия стационарные" от включенного тарифа
            $this->tariff_russia_id = $this->tariffRussia = $currentTariff->id_tarif_russia;
            // "Тариф Россия мобильные" от включенного тарифа
            $this->tariff_russia_mob_id = $this->tariffRussiaMobile = $currentTariff->id_tarif_russia_mob;
            // "Тариф Международка" от включенного тарифа
            $this->tariff_intern_id = $this->tariffIntern = $currentTariff->id_tarif_intern;

            // Устанавливает гарантированный платеж для набора (tariff_group_local_mob | tariff_group_russia | tariff_group_intern)
            $this->tariff_group_price = $currentTariff->minpayment_group;
            // "Тариф Местные мобильные" гарантированный платеж
            $this->tariff_group_local_mob_price = $currentTariff->minpayment_local_mob;
            // "Тариф Россия стационарные" гарантированный платеж
            $this->tariff_group_russia_price = $currentTariff->minpayment_russia;
            // "Тариф Международка" гарантированный платеж
            $this->tariff_group_intern_price = $currentTariff->minpayment_intern;

            $i = 0;
            while ($i < strlen($currentTariff->dest_group)) {
                $g = $currentTariff->dest_group[$i];
                switch ($g) {
                    case TariffVoip::DEST_LOCAL_MOBILE:
                        $this->tariff_group_local_mob = 1;
                        break;
                    case TariffVoip::DEST_INTERNATIONAL:
                        $this->tariff_group_intern = 1;
                        break;
                    case TariffVoip::DEST_RUSSIA:
                        $this->tariff_group_russia = 1;
                        break;
                }

                $i++;
            }

            $tariff = TariffVoip::findOne($this->tariff_main_id);
            // Устанавливает "Тип тарифа" от включенного "Тариф Основной"
            $this->tariff_main_status = $this->tariffMainStatus = $tariff->status;
        } else {
            $this->connecting_date = $this->today->format(DateTimeZoneHelper::DATE_FORMAT);
        }
    }

    /**
     * Переопределение родительского метода, вызывается в load
     */
    protected function preProcess()
    {
        if ($this->ndc_type_id != NdcType::ID_MCN_LINE && !NdcType::isCityDependent($this->ndc_type_id)) {
            $this->city_id = null;
        }

        if ($this->city_id) {
            $this->city = City::findOne($this->city_id);
            $this->connection_point_id = $this->city->connection_point_id;
        }

        if (!$this->tariff_main_status) {
            $this->tariff_main_status = TariffVoip::STATE_DEFAULT;
        }

        if (!$this->ndc_type_id) {
            $this->ndc_type_id = NdcType::ID_GEOGRAPHIC;
        }
    }

    /**
     * Установка зависимостей от типа услуги (номер, линия, 7800 etc)
     */
    public function processDependenciesNumber()
    {
        // сброс номера, если параметры формы не соответствуют номеру
        if ($this->did && $this->ndc_type_id != NdcType::ID_MCN_LINE) {
            /** @var \app\models\Number $number */
            $number = Number::findOne(['number' => $this->did]);
            if (
                !$number || (
                    $number->country_code != $this->country_id
                    || $number->city_id != $this->city_id
                    || $number->ndc_type_id != $this->ndc_type_id
                )
            ) {
                $this->did = null;
            }
        }

        switch ($this->ndc_type_id) {

            case NdcType::ID_MCN_LINE: {
                $this->did_group_id = null;
                if (strlen($this->did) < 4 || strlen($this->did) > 5) {
                    $this->did = UsageVoip::dao()->getNextLineNumber();
                }

                break;
            }

            case NdcType::ID_FREEPHONE: {
                $this->did_group_id = null;
                $this->ndc_type_id = NdcType::ID_FREEPHONE;

                if (!$this->did) {
                    /** @var \app\models\Number $number */
                    $number = (new FreeNumberFilter)
                        ->setCountry($this->country_id)
                        ->setNdcType(NdcType::ID_FREEPHONE)
                        ->randomOne();

                    if ($number) {
                        $this->did = $number->number;
                    }
                }

                // BIL-1442: У номеров 7800 тариф берется из папки 7800, или архив
                if (!in_array($this->tariff_main_status, [TariffVoip::STATUS_7800, TariffVoip::STATUS_7800_TEST, TariffVoip::STATUS_ARCHIVE])) {
                    $this->tariff_main_status = TariffVoip::STATUS_7800;
                }

                break;
            }

            default: {
                if (in_array($this->tariff_main_status, [TariffVoip::STATUS_7800, TariffVoip::STATUS_7800_TEST])) {
                    $this->tariff_main_status = TariffVoip::STATUS_PUBLIC;
                }

                if (!$this->did && ((NdcType::isCityDependent($this->ndc_type_id)) && $this->city_id || true) && $this->did_group_id) {
                    /** @var \app\models\Number $number */
                    $number = (new FreeNumberFilter)
                        ->setNdcType($this->ndc_type_id)
                        ->setCity($this->city_id)
                        ->setCountry($this->country_id)
                        ->setDidGroup($this->did_group_id)
                        ->randomOne();

                    if ($number) {
                        $this->did = $number->number;
                    }
                } elseif ($this->did) {
                    /** @var \app\models\Number $number */
                    $number = Number::findOne($this->did);

                    if (!$number) {
                        $this->did = null;
                    } else {
                        $this->city_id = $number->city_id;
                        $this->did_group_id = DidGroup::dao()->getIdByNumber($number);
                        $this->ndc_type_id = $number->ndc_type_id;
                    }
                }

                break;
            }
        }
    }

    /**
     * Сохранение тарифа
     *
     * @param UsageVoip $usage
     * @param string $tariffDate
     */
    private function _saveTariff(UsageVoip $usage, $tariffDate)
    {
        $destGroup = '';

        if ($this->tariff_group_local_mob) {
            $destGroup .= (string)TariffVoip::DEST_LOCAL_MOBILE;
        }

        if ($this->tariff_group_russia) {
            $destGroup .= (string)TariffVoip::DEST_RUSSIA;
        }

        if ($this->tariff_group_intern) {
            $destGroup .= (string)TariffVoip::DEST_INTERNATIONAL;
        }

        // Заполняем незаполненные минималки по тарифам
        if (!$this->tariff_group_russia_price) {
            $this->tariff_group_russia_price = $this->getMinByTariff($this->tariff_russia_id);
        }

        if (!$this->tariff_group_local_mob_price) {
            $this->tariff_group_local_mob_price = $this->getMinByTariff($this->tariff_local_mob_id);
        }

        if (!$this->tariff_group_intern_price) {
            $this->tariff_group_intern_price = $this->getMinByTariff($this->tariff_intern_id);
        }

        if ($this->mass_change_tariff) {
            $tariffUsages = [];
            foreach (UsageVoip::findAll(['client' => $usage->clientAccount->client]) as $otherUsage) {
                if ($otherUsage->isActive()) {
                    $tariffUsages[] = $otherUsage;
                }
            }

            $currentTariff = $usage->getLogTariff($tariffDate);
            $massChangeMainTariffId = $currentTariff->id_tarif;
        } else {
            $tariffUsages = [$usage];
            $massChangeMainTariffId = null;
        }

        /** @var \app\models\UsageVoip $tariffUsage */
        foreach ($tariffUsages as $tariffUsage) {
            $currentTariff = $tariffUsage->getLogTariff($tariffDate);

            if ($massChangeMainTariffId && $massChangeMainTariffId != $currentTariff->id_tarif) {
                continue;
            }

            if (
                !$currentTariff
                ||
                $this->tariff_main_id != $currentTariff->id_tarif
                ||
                $this->tariff_local_mob_id != $currentTariff->id_tarif_local_mob
                ||
                $this->tariff_russia_id != $currentTariff->id_tarif_russia
                ||
                $this->tariff_russia_mob_id != $currentTariff->id_tarif_russia_mob
                ||
                $this->tariff_intern_id != $currentTariff->id_tarif_intern
                ||
                $this->connecting_date != $currentTariff->date_activation
                ||
                $destGroup != $currentTariff->dest_group
                ||
                $this->tariff_group_price != $currentTariff->minpayment_group
                ||
                $this->tariff_group_local_mob_price != $currentTariff->minpayment_local_mob
                ||
                $this->tariff_group_russia_price != $currentTariff->minpayment_russia
                ||
                $this->tariff_group_intern_price != $currentTariff->minpayment_intern
            ) {
                $this->_logTarifUsage('usage_voip',
                    $tariffUsage->id, $tariffDate,
                    $this->tariff_main_id, $this->tariff_local_mob_id, $this->tariff_russia_id,
                    $this->tariff_russia_mob_id, $this->tariff_intern_id,
                    $destGroup, $this->tariff_group_price,
                    $this->tariff_group_local_mob_price, $this->tariff_group_russia_price,
                    $this->tariff_group_intern_price
                );

                Number::dao()->actualizeStatusByE164($tariffUsage->E164);
            }
        }
    }

    /**
     * Получение списка линий для 7800
     *
     * @param ClientAccount $clientAccount
     * @return array
     */
    public function getLinesFor7800(ClientAccount $clientAccount)
    {
        $tableName = UsageVoip::tableName();

        $list = UsageVoip::find()
            ->select([$tableName . '.E164', $tableName . '.id'])
            ->leftJoin($tableName . ' uv', 'uv.line7800_id = ' . $tableName . '.id')
            ->andWhere([$tableName . '.client' => $clientAccount->client])
            ->andWhere('LENGTH(' . $tableName . '.E164) >= 4')
            ->andWhere('LENGTH(' . $tableName . '.E164) <= 6')
            ->andWhere(['>', $tableName . '.actual_to', new Expression('DATE(NOW())')])
            ->andWhere(['uv.line7800_id' => null])
            ->orderBy([$tableName . '.E164' => SORT_ASC])
            ->indexBy('id')
            ->column();

        return $list;
    }

    /**
     * Сохранение истории
     *
     * @param array $cur
     * @param array $new
     * @param string $usage_name
     * @throws \Exception
     */
    private function _saveChangeHistory($cur = [], $new = [], $usage_name = '')
    {
        if (count($cur) == 0 || count($new) == 0 || !strlen($usage_name)) {
            return;
        }

        $fields = [];
        foreach ($cur as $field => $value) {
            if (isset($new[$field]) && $new[$field] != $value) {
                $fields[$field] = [
                    'value_from' => $value,
                    'value_to' => $new[$field]
                ];
            }
        }

        if (!count($fields)) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $history = new LogUsageHistory;
            $history->service = $usage_name;
            $history->service_id = $cur['id'];
            $history->user_id = Yii::$app->user->id;
            $history->save();

            if ($history->id) {
                $insert = [];
                foreach ($fields as $field => $value) {
                    $insert[] = [
                        $history->id,
                        $field,
                        $value['value_from'],
                        $value['value_to'],
                    ];
                }

                if (count($insert)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        LogUsageHistoryFields::tableName(),
                        ['log_usage_history_id', 'field', 'value_from', 'value_to'],
                        $insert
                    )->execute();
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * Сохранение тарифа в логе
     *
     * @param string $service
     * @param integer $id
     * @param string $dateActivation
     * @param integer $tarifId
     * @param integer $tarifLocalMobId
     * @param integer $tarifRussiaId
     * @param integer $tarifRussiaMobId
     * @param integer $tarifInternId
     * @param integer $destGroup
     * @param float $minpaymentGroup
     * @param float $minpaymentLocalMob
     * @param float $minpaymentRussia
     * @param float $minpaymentIntern
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function _logTarifUsage(
        $service,
        $id,
        $dateActivation,
        $tarifId,
        $tarifLocalMobId,
        $tarifRussiaId,
        $tarifRussiaMobId,
        $tarifInternId,
        $destGroup,
        $minpaymentGroup,
        $minpaymentLocalMob,
        $minpaymentRussia,
        $minpaymentIntern
    ) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $logTariff = new LogTarif;
            $logTariff->service = $service;
            $logTariff->id_service = $id;
            $logTariff->id_user = Yii::$app->user->id ?: 0;
            $logTariff->ts = (new DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $logTariff->date_activation = addslashes($dateActivation);
            $logTariff->comment = '';
            $logTariff->id_tarif = (int)$tarifId;
            $logTariff->id_tarif_local_mob = (int)$tarifLocalMobId;
            $logTariff->id_tarif_russia = (int)$tarifRussiaId;
            $logTariff->id_tarif_russia_mob = (int)$tarifRussiaMobId;
            $logTariff->id_tarif_intern = (int)$tarifInternId;
            $logTariff->dest_group = (int)$destGroup;
            $logTariff->minpayment_group = (int)$minpaymentGroup;
            $logTariff->minpayment_local_mob = (int)$minpaymentLocalMob;
            $logTariff->minpayment_russia = (int)$minpaymentRussia;
            $logTariff->minpayment_intern = (int)$minpaymentIntern;
            $logTariff->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Установка даты отключения
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function _setDisconnectionDate()
    {
        $timezone = $this->usage->clientAccount->timezone;
        if (!empty($this->disconnecting_date)) {
            $closeDate = new DateTime($this->disconnecting_date, $timezone);
            $this->usage->actual_to = $closeDate->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        $nextHistoryItems = LogTarif::find()
            ->andWhere(['service' => 'usage_voip'])
            ->andWhere(['id_service' => $this->usage->id])
            ->andWhere(['>', 'date_activation', $this->usage->actual_to])
            ->all();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->usage->save();

            foreach ($nextHistoryItems as $nextHistoryItem) {
                $nextHistoryItem->delete();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Получение минимального платежа от тарифа
     *
     * @param integer $tariffId
     * @return bool|string
     */
    public function getMinByTariff($tariffId)
    {
        return TariffVoip::find()
            ->select('month_min_payment')
            ->where(['id' => $tariffId])
            ->scalar();
    }
}