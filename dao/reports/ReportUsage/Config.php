<?php

namespace app\dao\reports\ReportUsage;

use app\models\billing\Hub;
use app\models\UsageTrunk;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use Yii;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;

class Config
{
    const ITEMS_MAX_SIZE = 50000;
    const ITEMS_PART_SIZE = 5000;

    const CONNECTION_MAIN = 1;
    const CONNECTION_ARCHIVE = 2;

    const TYPE_CALL = 'call';
    const TYPE_DAY = 'day';
    const TYPE_MONTH = 'month';
    const TYPE_YEAR = 'year';
    const TYPE_DEST = 'dest';

    const DESTINATION_ALL = 'all';
    const DESTINATION_LOCAL = '0';
    const DESTINATION_LOCAL_MOB = '0-m';
    const DESTINATION_LOCAL_FIX = '0-f';
    const DESTINATION_LOCAL_ZONE_FIX = '0-f-z';
    const DESTINATION_RUSSIA = '1';
    const DESTINATION_RUSSIA_MOB = '1-m';
    const DESTINATION_RUSSIA_FIX = '1-f';
    const DESTINATION_INT = '2';

    const DIRECTION_BOTH = 'both';
    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    protected static $types = [
        self::TYPE_CALL => 'звонкам',
        self::TYPE_DAY => 'дням',
        self::TYPE_MONTH => 'месяцам',
        self::TYPE_YEAR => 'годам',
        //self::TYPE_DEST => 'направлениям',
    ];

    protected static $destinations = [
//        self::DESTINATION_ALL => 'Все',
//        self::DESTINATION_LOCAL => 'Все местные вызовы',
//        self::DESTINATION_LOCAL_MOB => '&nbsp;&nbsp;Местные мобильные',
//        self::DESTINATION_LOCAL_FIX => '&nbsp;&nbsp;Местные стационарные',
//        self::DESTINATION_LOCAL_ZONE_FIX => '&nbsp;&nbsp;Зона - стационарные',
//        self::DESTINATION_RUSSIA => 'Россия',
//        self::DESTINATION_RUSSIA_MOB => '&nbsp;&nbsp;Россия мобильные',
//        self::DESTINATION_RUSSIA_FIX => '&nbsp;&nbsp;Россия стационарные',
//        self::DESTINATION_INT => 'Международные',
    ];

    protected static $directions = [
        self::DIRECTION_BOTH => 'Все',
        self::DIRECTION_IN => 'Входящие',
        self::DIRECTION_OUT => 'Исходящие',
    ];

    protected $usagesData = [];
    protected $trunksData = [];

    protected $usagesIds;
    protected $regionsIds;

    protected $usagesOptions;

    /** @var ClientAccount */
    public $account = null;
    public $isWithProfit = false;

    /** @var \DateTime */
    public $from;
    /** @var \DateTime */
    public $to;
    public $phone;
    public $type = self::TYPE_DAY;
    public $paidOnly = 0;
//    public $destination = self::DESTINATION_ALL;
    public $direction = self::DIRECTION_BOTH;
    public $isShowMax = false;
    public $timeZone;
    public $packages = [];
    public $marketPlace = Hub::MARKET_PLACE_ID_RUSSIA;

    public $errors = [];

    public function __construct($accountId)
    {
        $this->account = ClientAccount::findOne($accountId);

        if (!$this->account) {
            throw new \LogicException('Не выбран клиент!');
        }

        $this->timeZone = $this->account->timezone_name;
        $this->fetchUsagesAndTrunks();
        $this->validateUsages();
    }

    /**
     * Типы
     *
     * @return array
     */
    public function getTypes()
    {
        return self::$types;
    }

    /**
     * Направления
     *
     * @return array
     */
    public function getDestinations()
    {
        return self::$destinations;
    }

    /**
     * Входяшие / исходящие
     *
     * @return array
     */
    public function getDirections()
    {
        return self::$directions;
    }

    /**
     * Валидация
     *
     * @return bool
     */
    public function validate()
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->validateUsages();

        if (!array_key_exists($this->type, self::$types)) {
            $this->errors[] = [
                'type' => 'error',
                'text' => 'Неверное тип отчета',
            ];
        }
        if ($this->from >= $this->to) {
            $this->errors[] = [
                'type' => 'error',
                'text' => 'Неверный диапазон отчета',
            ];
        }
//        if (!array_key_exists($this->destination, self::$destinations)) {
//            $this->errors[] = [
//                'type' => 'error',
//                'text' => 'Неверное направление',
//            ];
//        }
        if (!array_key_exists($this->direction, self::$directions)) {
            $this->errors[] = [
                'type' => 'error',
                'text' => 'Неверный тип звонка',
            ];
        }
        if (!is_null($this->phone) && !array_key_exists($this->phone, $this->getUsagesOptions())) {
            $this->errors[] = [
                'type' => 'error',
                'text' => 'Неверный телефон',
            ];
        }

        return $this->isValid();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     *
     */
    public function validateUsages()
    {
        if (count($this->usagesData) || count($this->trunksData)) {
            return;
        };

        $this->errors[] = [
            'type' => 'error',
            'text' => 'У клиента нет подключенных телефонных номеров и транков.',
        ];
    }

    /**
     * Список услуг телефонии в ЛС
     *
     */
    protected function fetchUsagesAndTrunks()
    {
        $usages = [];
        $trunks = UsageTrunk::find()
            ->select(['id', 'trunk_id'])
            ->where(['client_account_id' => $this->account->id])
            ->asArray()
            ->all();

        if ($this->account->account_version == ClientAccount::VERSION_BILLER_USAGE) {
            $usages = UsageVoip::find()
                ->alias('u')
                ->select([
                    'id' => 'u.id',
                    'phone_num' => 'u.E164',
                    'region' => 'u.region',
                    'region_name' => 'r.name',
                    'timezone_name' => 'r.timezone_name'
                ])
                ->joinWith('connectionPoint r')
                ->client($this->account->client)
                ->orderBy([
                    'u.region' => SORT_DESC,
                    'u.id' => SORT_ASC
                ])
                ->asArray()
                ->all();

        } elseif ($this->account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $accountTariffs = AccountTariff::find()
                ->where([
                    'client_account_id' => $this->account->id,
                    'service_type_id' => ServiceType::ID_VOIP
                ])
                ->with('city', 'region', 'number.regionModel')
                ->orderBy([
                    AccountTariff::tableName() . '.id' => SORT_ASC
                ]);

            $usages = [];
            /** @var AccountTariff $accountTariff */
            foreach ($accountTariffs->all() as $accountTariff) {
                $region = $accountTariff->number->regionModel;
                $usages[] = [
                    'id' => $accountTariff->id,
                    'phone_num' => $accountTariff->voip_number,
                    'region' => $region->id,
                    'region_name' => $region->name,
                    'timezone_name' => $region->timezone_name
                ];
            }

            usort($usages, function ($a, $b) {
                if ($a['region'] == $b['region']) {
                    if ($a['phone_num'] == $b['phone_num']) {
                        return 0;
                    }

                    return $a['phone_num'] > $b['phone_num'] ? 1 : -1;
                }

                return $a['region'] < $b['region'] ? 1 : -1;
            });
        }

        $this->usagesData = $usages;
        $this->trunksData = $trunks;
    }

    /**
     * Получение таймзон услуг
     *
     * @return array
     */
    public function getTimezones()
    {
        $timezones = [
            $this->account->timezone_name => 1,
            DateTimeZoneHelper::TIMEZONE_UTC => 1
        ];

        foreach ($this->usagesData as $usage) {
            $timezones[$usage['timezone_name']] = 1;
        }

        return array_keys($timezones);
    }

    /**
     * Получение регионов
     *
     * @return array
     */
    protected function getRegions()
    {
        $regions = [];
        foreach ($this->usagesData as $usage) {
            if (isset($regions[$usage['region']])) {
                continue;
            }

            $regions[$usage['region']] = $usage['region'];
        }

        return $regions;
    }

    /**
     * Список услуг телефонии и транки
     *
     * @return array
     */
    protected function prepareOptions()
    {
        $select = [];

        $regions = $this->getRegions();
        if (count($regions) > 1) {
            $select[] = [
                'type' => 'usage',
                'is_all' => true,
            ];
        }

        $lastRegion = '';
        foreach ($this->usagesData as $usage) {
            if ($lastRegion != $usage['region']) {
                $select[] = [
                    'type' => 'usage',
                    'region' => $usage['region'],
                    'region_name' => $usage['region_name'],
                    'is_all' => 1,
                ];
                $lastRegion = $usage['region'];
            }

            $select[] = [
                'type' => 'usage',
                'region' => $usage['region'],
                'region_name' => $usage['region_name'],
                'is_all' => false,
                'id' => $usage['id'],
                'value' => $usage['phone_num'],
            ];
        }

        if ($this->trunksData) {
            $select[] = [
                'type' => 'trunk',
                'is_all' => true,
            ];
        }

        foreach ($this->trunksData as $trunk) {
            $select[] = [
                'type' => 'trunk',
                'is_all' => false,
                'id' => $trunk['id'],
                'value' => $trunk['id'],
            ];
        }

        return $select;
    }

    /**
     * Конвертирует список услуг в данные для select'а
     *
     * @param bool $isAsTemplate
     * @return string[]
     */
    public function getUsagesOptions($isAsTemplate = false)
    {
        if (!is_null($this->usagesOptions)) {
            return $this->usagesOptions;
        }

        $convertData = $this->prepareOptions();

        $tr = $isAsTemplate ?
            function ($str) {
                return "{" . $str . "}";
            } : // переводим в шаблоны и переводит ЛК
            function ($str) {
                return Yii::t('number', $str);
            }; // переводим внутри стата

        $this->usagesOptions = [];
        foreach ($convertData as $type => $usage) {
            $key = $usage['type'] . '_' . (isset($usage['region']) ? $usage['region'] : '');

            ($usage['is_all'] || isset($usage['id'])) && $key .= '_' . ($usage['is_all'] ? 'all' : $usage['value']);

            if ($usage['type'] == 'usage') {
                if ($usage['is_all']) {
                    $value = isset($usage['region']) ? $usage['region_name'] . ' (' . $tr('All numbers') . ')' : $tr('All regions');
                } else {
                    $value = '&nbsp;&nbsp;' . $usage['value'];
                }
            } else { // trunk
                $value = ($usage['is_all'] ? $tr('All trunks') : $tr('Trunk') . ' #' . $usage['id']);
            }

            $this->usagesOptions[$key] = $value;
        }

        return $this->usagesOptions;
    }

    /**
     * Разбор полученного значения
     *
     * @param string $selected
     * @return array
     */
    protected function parseSelected($selected)
    {
        $e = explode('_', $selected);
        list($type, $region, $value) = $e;

        $data = ['type' => $type];

        if ($value == 'all') {
            return $data + [
                    'is_all' => true
                ] + ($region ? ['region' => $region] : []);
        }

        $data['is_all'] = false;
        $data['value'] = $value;

        return $data;
    }

    /**
     * @return array
     */
    public function getUsagesIds()
    {
        if (is_null($this->usagesIds)) {
            $this->fillParams();
        }

        return $this->usagesIds;
    }

    /**
     * @return array
     */
    public function getRegionsIds()
    {
        if (is_null($this->regionsIds)) {
            $this->fillParams();
        }

        return $this->regionsIds;
    }

    /**
     * Заполняем настройки отчета
     */
    protected function fillParams()
    {
        $parsedData = $this->parseSelected($this->phone);

        $usageIds = [];
        $regions = [];
        $isTrunk = false;

        if ($parsedData['type'] == 'usage') {
            foreach ($this->usagesData as $usage) {
                if (
                    ($parsedData['is_all'] &&
                        (isset($parsedData['region']) ? $parsedData['region'] == $usage['region'] : true)
                    ) ||
                    ($usage['phone_num'] == $parsedData['value'])
                ) {
                    $usageIds[] = $usage['id'];
                    $regions[$usage['region']] = 1;
                }
            }
        } else {
            // trunk
            foreach ($this->trunksData as $trunk) {
                if ($parsedData['is_all'] || $trunk['id'] == $parsedData['value']) {
                    $usageIds[] = $trunk['id'];
                }

                $isTrunk = true;
            }
        }

        if ($isTrunk) {
            $regions = 'trunk';
        } else {
            $regions = array_keys($regions);
        }

        $this->usagesIds = $usageIds;
        $this->regionsIds = $regions;
    }
}