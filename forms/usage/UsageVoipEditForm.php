<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Assert;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use app\models\City;
use app\models\LogTarif;
use app\models\Number;
use app\models\TariffVoip;
use app\models\UsageVoip;
use app\models\ClientAccount;
use app\models\TariffNumber;
use app\models\usages\UsageInterface;
use app\models\LogUsageHistory;

class UsageVoipEditForm extends UsageVoipForm
{
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
        $tariffIntern;

    private static $mapPriceToId = [
        'tariff_group_intern_price' => 'tariff_intern_id',
        'tariff_group_russia_price' => 'tariff_russia_id',
        'tariff_group_local_mob_price' => 'tariff_local_mob_id',
    ];


    public function rules($appendRules = [])
    {
        $rules = parent::rules();
        $rules[] = [['no_of_lines'], 'default', 'value' => 1];
        $rules[] = [[
            'type_id', 'city_id', 'client_account_id',
            'no_of_lines', 'did',
            'tariff_main_id', 'tariff_local_mob_id', 'tariff_russia_id', 'tariff_russia_mob_id', 'tariff_intern_id',
        ], 'required', 'on' => 'add'];
        $rules[] = [['did'], 'trim'];
        $rules[] = [['did'], 'validateDid', 'on' => 'add'];
        $rules[] = [['address', 'disconnecting_date'], 'string', 'on' => 'edit'];

        $rules[] = [[
            'no_of_lines',
            'tariff_main_id', 'tariff_local_mob_id', 'tariff_russia_id', 'tariff_russia_mob_id', 'tariff_intern_id',
        ], 'required', 'on' => 'change-tariff'];

        $rules[] = [[
            'tariff_group_intern_price', 'tariff_group_russia_price', 'tariff_group_local_mob_price'
        ], 'validateMinTariff'];

        $rules[] = [['number_tariff_id'], 'required', 'on' => 'add', 'when' => function($model) { return $model->type_id === 'number'; }];
        $rules[] = [['line7800_id'], 'required', 'on' => 'add', 'when' => function($model) { return $model->type_id === '7800'; }];
        $rules[] = [['line7800_id'], 'validateNoUsedLine', 'on' => 'add', 'when' => function($model) { return $model->type_id === '7800'; }];

        return $rules;
    }

    /**
    * Валидация гарантированных платежей
    *
    * @param string $attribute
    * @param [] $params
    */
    public function validateMinTariff($attribute, $params)
    {
        $field = static::$mapPriceToId[$attribute];
        $val = $this->getMinByTariff($this->$field);
        if (($val > 0) && ($this->$attribute == 0)) {
            $this->addError($attribute, 'Минимальный платеж не должен быть в этом тарифе');
            return;
        }
    }

    /**
     * Валидация если "Тип" = Линия
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validateNoUsedLine($attribute, $params)
    {
        if (!UsageVoip::findOne(['client' => $this->clientAccount->client, 'id' => $this->$attribute])) {
            $this->addError('line7800_id', 'Линия не найдена');
        }

        if (UsageVoip::findOne(['client' => $this->clientAccount->client, 'line7800_id' => $this->$attribute])) {
            $this->addError('line7800_id', 'Линия подключена к другому номеру');
        }
    }

    /**
     * Валидация номера
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validateDid($attribute, $params)
    {
        if (!$this->did) {
            return;
        }

        switch ($this->type_id) {
            case 'number': {
                if (!preg_match('/^\d+$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }

                /** @var \app\models\Number $number */
                $number = Number::findOne($this->did);
                if ($number === null) {
                    $this->addError('did', 'Номер не найден');
                }
                else if (
                    $number->status != Number::STATUS_INSTOCK
                    && $number->status != Number::STATUS_HOLD
                    && !($number->status == Number::STATUS_RESERVED && $number->client_id == $this->clientAccount->id)
                    && $number->status != Number::STATUS_ACTIVE //контроль переноса номера в статусе "активный" регулируется далее
                ) {
                    $msg = 'Номер находится в статусе "' . Number::$statusList[$number->status] . '"';

                    if ($number->status == Number::STATUS_RESERVED || $number->status == Number::STATUS_ACTIVE) {
                        $msg .= ', Л/С: ' . $number->client_id;
                    }

                    $this->addError('did', $msg);
                }

                if ($number && $number->city_id != $this->city_id) {
                    $this->addError('did', 'Номер ' . $this->did . ' из другого города');
                }
                break;
            }

            case 'line': {
                if (!preg_match('/^\d{4,5}$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }
                break;
            }

            case 'operator': {
                if (!preg_match('/^\d{3}$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
                }
                break;
            }

            case '7800': {
                if (!preg_match('/^7800\d{7}$/', $this->did)) {
                    $this->addError('did', 'Неверный формат номера');
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
        foreach(static::$mapPriceToId as $fieldMinPrice => $fieldTariffId) {
            $minimalPayment = $this->getMinByTariff($this->$fieldTariffId);

            if ($this->usage) {
                $this->{Inflector::variablize($fieldMinPrice)} = $minimalPayment;

                if(
                    (
                        $this->tariff_local_mob_id != $this->tariffLocalMobile
                        &&
                        $fieldTariffId === 'tariff_local_mob_id'
                    )
                        ||
                    (
                        $this->tariff_russia_id != $this->tariffRussia
                        &&
                        $fieldTariffId === 'tariff_russia_id'
                    )
                        ||
                    (
                        $this->tariff_russia_mob_id != $this->tariffRussiaMobile
                        &&
                        $fieldTariffId === 'tariff_russia_mob_id'
                    )
                        ||
                    (
                        $this->tariff_intern_id != $this->tariffIntern
                        &&
                        $fieldTariffId === 'tariff_intern_id'
                    )
                ) {
                    $this->$fieldMinPrice = $minimalPayment;
                }
            }

            if (!$this->usage || $this->tariff_main_status != $this->tariffMainStatus) {
                $this->$fieldMinPrice = $minimalPayment;
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

        $usage = new UsageVoip;
        $usage->region = $this->connection_point_id;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->type_id = $this->type_id;
        $usage->client = $this->clientAccount->client;
        $usage->E164 = $this->did;
        $usage->no_of_lines = (int) $this->no_of_lines;
        $usage->status = $this->status;
        $usage->address = $this->address;
        $usage->edit_user_id = Yii::$app->user->getId();
        $usage->line7800_id = $this->type_id === '7800' ? $this->line7800_id : 0;
        $usage->is_trunk = $this->type_id === 'operator' ? 1 : 0;
        $usage->one_sip = 0;
        $usage->create_params = $this->create_params;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->saveChangeHistory($usage->oldAttributes, $usage->attributes, 'usage_voip');

            $usage->save();

            $this->saveTariff($usage, $this->connecting_date);

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $usage->id;

        return true;
    }

    /**
     * Предустановка данных (scenario = "add")
     */
    public function prepareAdd()
    {
        if ($this->did) {
            $this->type_id = 'number';

            if (strlen($this->did) >= 4 && strlen($this->did) <= 5) {
                $this->type_id = 'line';
            }
            else if (substr($this->did, 0, 4) === '7800') {
                $this->type_id = '7800';
            }

            if ($this->type_id !== 'number') {
                $this->number_tariff_id = null;
            }
            else {
                /** @var \app\models\Number $number */
                $number = Number::findOne(['number' => $this->did]);

                if ($number) {
                    $tarifNumber = TariffNumber::findOne(['did_group_id' => $number->did_group_id]);

                    if ($tarifNumber) {
                        $this->connection_point_id = $number->region;
                        $this->number_tariff_id = $tarifNumber->id;
                        $this->city_id = $number->city_id;
                    }
                    else {
                        $this->did = null;
                    }
                }
                else {
                    $this->did = null;
                }
            }
        }
        else {
            if ($this->type_id === 'line') {
                $this->did = UsageVoip::dao()->getNextLineNumber();
            }
        }

        if ($this->clientAccount && !$this->connection_point_id) {
            $this->connection_point_id = $this->clientAccount->region;
        }

        if (!$this->connecting_date) {
            $this->connecting_date = date('Y-m-d');
        }

        if (!$this->tariff_local_mob_id && $this->clientAccount && $this->connection_point_id) {
            $whereTariffVoip = [
                'status'              => 'public',
                'connection_point_id' => $this->connection_point_id,
                'currency_id'         => $this->clientAccount->currency
            ];

            $this->tariff_local_mob_id  = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_LOCAL_MOBILE])->scalar();
            $this->tariff_russia_id     = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_RUSSIA])->scalar();
            $this->tariff_intern_id     = TariffVoip::find()->select('id')->andWhere($whereTariffVoip)->andWhere(['dest' => TariffVoip::DEST_INTERNATIONAL])->scalar();
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
        $activationDt = (new DateTime($actualFrom, $this->timezone))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        $this->usage->actual_from = $actualFrom;
        $this->usage->activation_dt = $activationDt;
        $this->usage->status = $this->status;
        $this->usage->address = $this->address;
        $this->usage->no_of_lines = (int) $this->no_of_lines;

        if (empty($this->disconnecting_date) && $this->usage->actual_to != UsageInterface::MAX_POSSIBLE_DATE) {
            $this->disconnecting_date = UsageInterface::MAX_POSSIBLE_DATE;
        }
        if ($this->usage->actual_to != $this->disconnecting_date) {
            $this->setDisconnectionDate();
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->saveChangeHistory($this->usage->oldAttributes, $this->usage->attributes, 'usage_voip');

            $this->usage->save();

            $transaction->commit();
        }
        catch (\Exception $e) {
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
            $this->saveTariff($this->usage, $this->tariff_change_date);

            $transaction->commit();
        }
        catch (\Exception $e) {
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

        // Если услуга известна (scenario = edit)
        if ($usage) {
            $this->usage = $usage;
            $this->id = $usage->id;
            $this->connection_point_id = $usage->region;
            $this->connecting_date = $usage->actual_from;
            $this->disconnecting_date = (new DateTime($usage->actual_to))->format('Y') === '4000' ? '' : $usage->actual_to;
            $this->tariff_change_date = $this->today->format('Y-m-d');

            $this->setAttributes($usage->getAttributes(), false);
            $this->did = $usage->E164;
            if ($usage->line7800 !== null) {
                $this->line7800_id = $usage->line7800->E164;
            }

            // Включенный на услуге тариф
            $currentTariff = $usage->logTariff;

            // "Тариф Основной" от включенного тарифа
            $this->tariff_main_id = $currentTariff->id_tarif;
            // "Тариф Местные мобильные" от включенного тарифа
            $this->tariff_local_mob_id =  $this->tariffLocalMobile = $currentTariff->id_tarif_local_mob;
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
        }
        else {
            $this->connecting_date = $this->today->format('Y-m-d');
        }
    }

    /**
     * Переопределение родительского метода, вызывается в load
     */
    protected function preProcess()
    {
        if ($this->city_id) {
            $this->city = City::findOne($this->city_id);
            $this->connection_point_id = $this->city->connection_point_id;
        }

        if (!$this->tariff_main_status) {
            $this->tariff_main_status = TariffVoip::STATE_DEFAULT;
        }

        if (!$this->type_id) {
            $this->type_id = 'number';
        }
    }

    /**
     * Установка зависимостей от типа услуги (номер, линия, 7800 etc)
     */
    public function processDependenciesNumber()
    {
        // type_id => [number, line, 7800, operator]

        switch($this->type_id) {
            case 'number': {
                if ($this->city_id && $this->number_tariff_id && !$this->did) {
                    $numberTariff = TariffNumber::findOne($this->number_tariff_id);
                    /** @var \app\models\Number $number */
                    $number = Number::dao()->getRandomFreeNumber($numberTariff->did_group_id);
                    if ($number) {
                        $this->did = $number->number;
                    }
                }
                else {
                    if ($this->did) {
                        /** @var \app\models\Number $number */
                        $number = Number::findOne($this->did);

                        if (!$number) {
                            $this->did = null;
                        }
                        else {

                            $numberTariff = TariffNumber::findOne(['did_group_id' => $number->did_group_id]);

                            if ($numberTariff) {
                                if ($this->city_id != $number->city_id || $this->number_tariff_id != $numberTariff->id) {
                                    $this->city_id = $number->city_id;
                                    $this->number_tariff_id = $numberTariff->id;
                                }
                            }
                            else {
                                $this->did = null;
                            }
                        }
                    }
                }
                break;
            }

            case 'line': {
                $this->number_tariff_id = null;
                if (strlen($this->did) < 4 || strlen($this->did) > 5) {
                    $this->did = UsageVoip::dao()->getNextLineNumber();
                }
                break;
            }

            case '7800': {
                $this->number_tariff_id = null;

                if (!$this->did) {
                    $number = Number::dao()->getRandomFree7800();
                    if ($number) {
                        $this->did = $number->number;
                    }
                }

                //BIL-1442: У номеров 7800 тариф берется из папки 7800, или архив
                if (!in_array($this->tariff_main_status, [TariffVoip::STATE_7800, TariffVoip::STATE_STORE])) {
                    $this->tariff_main_status = TariffVoip::STATE_7800;
                }

                if (substr($this->did, 0, 4) != '7800') {
                    $this->did = null;
                }
                break;
            }

            case 'operator': {
                $this->number_tariff_id = null;
                if (strlen($this->did) != 3) {
                    $this->did =
                        UsageVoip::find()
                            ->select(['number' => 'MAX(CONVERT(E164,UNSIGNED INTEGER))+1'])
                            ->where('LENGTH(E164)=3')
                            ->scalar();
                }
                break;
            }
        }
    }

    /**
     * Сохранение тарифа
     *
     * @param UsageVoip $usage
     * @param $tariffDate
     */
    private function saveTariff(UsageVoip $usage, $tariffDate)
    {
        $destGroup = '';

        if ($this->tariff_group_local_mob) {
            $destGroup .= (string) TariffVoip::DEST_LOCAL_MOBILE;
        }
        if ($this->tariff_group_russia) {
            $destGroup .= (string) TariffVoip::DEST_RUSSIA;
        }
        if ($this->tariff_group_intern) {
            $destGroup .= (string) TariffVoip::DEST_INTERNATIONAL;
        }

        if ($this->mass_change_tariff) {
            $tariffUsages = [];
            foreach(UsageVoip::findAll(['client' => $usage->clientAccount->client]) as $otherUsage) {
                if ($otherUsage->isActive()) {
                    $tariffUsages[] = $otherUsage;
                }
            }

            $currentTariff = $usage->getLogTariff($tariffDate);
            $massChangeMainTariffId = $currentTariff->id_tarif;
        }
        else {
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
                $this->logTarifUsage('usage_voip',
                    $tariffUsage->id, $tariffDate,
                    $this->tariff_main_id, $this->tariff_local_mob_id, $this->tariff_russia_id, $this->tariff_russia_mob_id, $this->tariff_intern_id,
                    $destGroup, $this->tariff_group_price,
                    $this->tariff_group_local_mob_price, $this->tariff_group_russia_price, $this->tariff_group_intern_price
                );
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

        $query =
            UsageVoip::find()
                ->leftJoin($tableName . ' uv', 'uv.`line7800_id` = ' . $tableName . '.`id`')
                ->andWhere([$tableName . '.`client`' => $clientAccount->client])
                ->andWhere('LENGTH(`' . $tableName . '`.`E164`) >= 4')
                ->andWhere('LENGTH(`' . $tableName . '`.`E164`) <= 6')
                ->andWhere($tableName . '.`actual_to` > DATE(NOW())')
                ->andWhere(['uv.`line7800_id`' => null]);

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy($tableName . '.`id`')
                    ->asArray()
                    ->all(),
                'id',
                'E164'
            );

        return $list;
    }

    /**
     * Сохранение истории
     *
     * @param [] $cur
     * @param [] $new
     * @param string $usage_name
     * @throws \yii\db\Exception
     */
    private function saveChangeHistory($cur = [], $new = [], $usage_name = '')
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
                        \app\models\LogUsageHistoryFields::tableName(),
                        ['log_usage_history_id', 'field', 'value_from', 'value_to'],
                        $insert
                    )->execute();
                }
            }

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * Сохранение тарифа в логе
     *
     * @param $service
     * @param $id
     * @param $dateActivation
     * @param $tarifId
     * @param $tarifLocalMobId
     * @param $tarifRussiaId
     * @param $tarifRussiaMobId
     * @param $tarifInternId
     * @param $destGroup
     * @param $minpaymentGroup
     * @param $minpaymentLocalMob
     * @param $minpaymentRussia
     * @param $minpaymentIntern
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function logTarifUsage(
        $service,$id,$dateActivation,
        $tarifId,$tarifLocalMobId,$tarifRussiaId,$tarifRussiaMobId,$tarifInternId,
        $destGroup, $minpaymentGroup,
        $minpaymentLocalMob, $minpaymentRussia, $minpaymentIntern
    ) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $logTariff = new LogTarif;
            $logTariff->service = $service;
            $logTariff->id_service = $id;
            $logTariff->id_user = Yii::$app->user->id ?: 0;
            $logTariff->ts = (new DateTime('now'))->format(DateTime::ATOM);
            $logTariff->date_activation = addslashes($dateActivation);
            $logTariff->comment = '';
            $logTariff->id_tarif = (int) $tarifId;
            $logTariff->id_tarif_local_mob = (int) $tarifLocalMobId;
            $logTariff->id_tarif_russia = (int) $tarifRussiaId;
            $logTariff->id_tarif_russia_mob = (int) $tarifRussiaMobId;
            $logTariff->id_tarif_intern = (int) $tarifInternId;
            $logTariff->dest_group = (int) $destGroup;
            $logTariff->minpayment_group = (int) $minpaymentGroup;
            $logTariff->minpayment_local_mob = (int) $minpaymentLocalMob;
            $logTariff->minpayment_russia = (int) $minpaymentRussia;
            $logTariff->minpayment_intern = (int) $minpaymentIntern;
            $logTariff->save();

            $transaction->commit();
        }
        catch (\Exception $e) {
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
    private function setDisconnectionDate()
    {
        $timezone = $this->usage->clientAccount->timezone;
	if (!empty($this->disconnecting_date)) {
            $closeDate = new DateTime($this->disconnecting_date, $timezone);
            $this->usage->actual_to = $closeDate->format('Y-m-d');
	}

        $nextHistoryItems =
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip'])
                ->andWhere(['id_service' => $this->usage->id])
                ->andWhere('date_activation > :date', [':date' => $this->usage->actual_to])
                ->all();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->usage->save();

            foreach ($nextHistoryItems as $nextHistoryItem) {
                $nextHistoryItem->delete();
            }

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Получение минимального платежа от тарифа
     *
     * @param $tariffId
     * @return bool|string
     */
    private function getMinByTariff($tariffId)
    {
        return
            TariffVoip::find()
                ->select('month_min_payment')
                ->where(['id' => $tariffId])
                ->scalar();
    }

}
