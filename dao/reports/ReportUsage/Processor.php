<?php

namespace app\dao\reports\ReportUsage;

use app\dao\reports\ReportUsage\Processor\StatisticsDestinations;
use app\dao\reports\ReportUsage\Processor\StatisticsVoip;
use app\models\billing\CallsCdr;
use app\models\billing\Clients;
use app\models\billing\CurrencyRate;
use app\models\billing\Hub;
use app\models\billing\Server;
use app\models\UsageTrunk;
use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\CallsRaw;
use app\models\billing\Geo;
use app\models\billing\InstanceSettings;

abstract class Processor implements ProcessorInterface
{
    protected static $processorsMap = [
        Config::TYPE_CALL => StatisticsVoip::class,
        Config::TYPE_DAY => StatisticsVoip::class,
        Config::TYPE_MONTH => StatisticsVoip::class,
        Config::TYPE_YEAR => StatisticsVoip::class,
        Config::TYPE_DEST => StatisticsDestinations::class,
    ];

    /** @var Config */
    protected $config;

    protected $rateCur1 = '';
    protected $rateCur2 = '';

    protected $rateTax1 = '';
    protected $rateTax2 = '';
    protected $rateTaxWith1 = '';
    protected $rateTaxWith2 = '';

    protected $result = [];

    /** @var ActiveQuery */
    protected $query;

    /**
     * Processor constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        if (!$this->config) {
            throw new \LogicException('Config for processor not set!');
        }
    }

    /**
     * @param Config $config
     * @return static
     */
    public static function createFromConfig(Config $config)
    {
        if (empty(self::$processorsMap[$config->type])) {
            throw new \LogicException('Unknown processor for report type: ' . $config->type);
        }

        $processorClass = self::$processorsMap[$config->type];

        return new $processorClass($config);
    }

    /**
     * @return ClientAccount
     */
    protected function getAccount()
    {
        return $this->config->account;
    }

    /**
     * @return bool
     */
    protected function isWithProfit()
    {
        return $this->config->isWithProfit;
    }

    /**
     * Пре-обработка
     * @throws \Exception
     */
    public function processBefore()
    {
        // to override
        $this->query = $this->initQuery();
    }

    /**
     * Получение, разбивка и обработка данных статистики
     *
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function processItems()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    /**
     * Получение данных
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function getItems()
    {
        return $this->getItemsBase();
    }

    /**
     * Обработчик записи
     *
     * @param array $item
     */
    public function processItem(array $item)
    {
        // to override
    }

    /**
     * Пост-обработка
     */
    public function processAfter()
    {
        // to override
    }

    /**
     * Статистика по телефонии
     *
     * @return array
     * @throws \Exception
     */
    public function getResult()
    {
        if (!$this->config->getUsagesIds()) {
            return [];
        }

        $this->result = [];
        $this->processBefore();
        $this->processItems();
        $this->processAfter();

        return $this->result;
    }


    // ************************************************************************
    // Processor base functions

    /**
     * @return ActiveQuery
     * @throws \Exception
     */
    protected function initQuery()
    {
        // Set params
        $accountId = $this->getAccount()->id;

//        $destination = $this->config->destination;
        $direction = $this->config->direction;
        $marketPlaceId = $this->config->marketPlace;

        $usageIds  = $this->config->getUsagesIds();
        $regions = $this->config->getRegionsIds();

        // Se conditions
        if (!$this->isWithProfit()) {
            $query = CallsRaw::find()
                ->alias('cr')
                ->andWhere(['account_id' => $accountId]);
        } else {
            // currency rates
            $this->rateCur1 = $this->getAccount()->currency != 'RUB' ? ' * COALESCE(rate1.rate, 1)' : '';
            $this->rateCur2 = ' * COALESCE(rate2.rate, 1)';

            // tax rate
            $rateTax1 = $this->getAccount()->getTaxRate();
            if ($this->getAccount()->price_include_vat) {
                $rateTax1 = floatval(100 / (100 + $rateTax1));
                $rateTaxWith1 = 1;
            } else {
                $rateTax1 = 1;
                $rateTaxWith1 = floatval((100 + $rateTax1) / 100);
            }

            $this->rateTax1 = $rateTax1 != 1 ? ' * ' . $rateTax1 : '';
            $this->rateTaxWith1 = $rateTaxWith1 != 1 ? ' * ' . $rateTaxWith1 : '';

            $this->rateTax2 =
                '* COALESCE((CASE WHEN c2.price_include_vat IS TRUE THEN (100.0/(100.0 + c2.effective_vat_rate))::decimal ELSE 1 END), 1)';
            $this->rateTaxWith2 =
                '* COALESCE((CASE WHEN c2.price_include_vat IS FALSE THEN ((100.0 + c2.effective_vat_rate)/100.0)::decimal ELSE 1 END), 1)';

            $query = CallsCdr::find()
                ->alias('cdr')

                ->innerJoin(['s' => Server::tableName()], 's.id = cdr.server_id')
                ->innerJoin([
                    'h' => Hub::tableName()],
                    'h.id = s.hub_id' .
                    ' AND h.market_place_id = ' . $marketPlaceId
                )
                ->innerJoin(['cr' => CallsRaw::tableName()], 'cr.cdr_id = cdr.id')

                //->innerJoin(['cdr2' => CallsCdr::tableName()], 'cdr2.mcn_callid = cdr.mcn_callid')
                ->leftJoin(['cdr2' => CallsCdr::tableName()],
                    'cdr2.mcn_callid = cdr.mcn_callid' .
                        ' AND (cdr2.mcn_callid IS NOT NULL)' .
                        ' AND (cdr2.session_time > 0)'
                )
                ->leftJoin(['s2' => Server::tableName()], 's2.id = cdr2.server_id')
                ->leftJoin(['h2' => Hub::tableName()],
                    'h2.id = s2.hub_id' .
                        ' AND h2.market_place_id = ' . $marketPlaceId
                )

                //->innerJoin(['cr2' => CallsRaw::tableName()], 'cr2.cdr_id = cdr2.id AND (cr2.orig = cr.orig) IS FALSE')
                ->leftJoin(['cr2' => CallsRaw::tableName()], 'cr2.cdr_id = cdr2.id AND (cr2.orig = cr.orig) IS FALSE AND (cr2.account_id IS NOT NULL)')
            ;

            if ($this->getAccount()->currency != 'RUR') {
                $query
                    ->leftJoin(
                        ['rate1' => CurrencyRate::tableName()],
                        'rate1.currency::public.currencies = \'' . $this->getAccount()->currency . '\' AND rate1.date = now()::date'
                    );
            }

            $query
                ->leftJoin(['c2' => Clients::tableName()], 'c2.id = cr2.account_id')
                ->leftJoin(['rate2' => CurrencyRate::tableName()], 'rate2.currency::public.currencies = c2.currency AND rate2.date = now()::date')

                ->andWhere(['IS NOT', 'cdr.mcn_callid', null])
//                ->andWhere(['>', 'cdr.session_time', 0])

                //->andWhere(['IS NOT', 'cdr2.mcn_callid', null])
                //->andWhere(['>', 'cdr2.session_time', 0])

                ->andWhere(['cr.account_id' => $accountId])
                //->andWhere(['IS NOT', 'cr2.account_id', null])
            ;
        }

        $direction !== Config::DIRECTION_BOTH && $query->andWhere(['cr.orig' => ($direction === Config::DIRECTION_IN ? 'false' : 'true')]);
        count($usageIds) && $query->andWhere([($regions == 'trunk' ? 'cr.trunk_service_id' : 'cr.number_service_id') => $usageIds]);
        $this->config->paidOnly && $query->andWhere('ABS(cr.cost) > 0.0001');

        // Статистика по транкам - смотрится по транкам.
        // Звонки по услугам могут быть привязаны к мультитранкам.
        $regions == 'trunk' && $query->andWhere(['cr.number_service_id' => null]);

        // Если есть мультитранк, то фильтруем входящие по транку клиента
        ClientAccount::dao()->isMultitrunkAccount($accountId) && $query
            ->andWhere([
                'OR',
                'cr.orig',
                [
                    'cr.trunk_service_id' => UsageTrunk::find()
                        ->andWhere(['client_account_id' => $accountId])
                        ->select('id')
                        ->column()
                ]
            ]);

//        if ($destination !== Config::DESTINATION_ALL) {
//            list ($dest, $mobile, $zone) = explode('-', $destination);
//
//            if ((int)$dest == 0) {
//                $query->andWhere(['<=', 'cr.destination_id', (int)$dest]);
//            } else {
//                $query->andWhere(['cr.destination_id' => (int)$dest]);
//            }
//
//            switch ($mobile) {
//                case 'm':
//                    $query->andWhere('cr.mob');
//                    break;
//                case 'f':
//                    $query->andWhere('NOT cr.mob');
//                    break;
//            }
//
//            if ((int)$dest == 0 && $mobile === 'f') {
//                $query
//                    ->leftJoin(['iss' => InstanceSettings::tableName()], 'iss.city_geo_id = cr.geo_id')
//                    ->leftJoin(['g' => Geo::tableName()], 'g.id = iss.city_geo_id');
//
//                switch ($zone) {
//                    case 'z':
//                        $query->andWhere('g.id IS NULL');
//                        break;
//                    default:
//                        $query->andWhere('g.id IS NOT NULL');
//                        break;
//                }
//            }
//        }

        return $query;
    }

    /**
     * Получение базового запроса для ридера
     *
     * @param int $connectionId
     * @return ActiveQuery
     */
    protected function getBaseQueryByConnectionId($connectionId)
    {
        $query = clone $this->query;
        return $query;
    }

    /**
     * Создаем команду для выполнения
     *
     * @param DateTime $from
     * @param DateTime $to
     * @param integer $connectionId
     * @param integer $inCount
     *
     * @return \yii\db\Command|null
     */
    protected function makeQueryCommand(
        DateTime $from,
        DateTime $to,
        $connectionId,
        &$inCount
    )
    {
        $limit = $this->config->isShowMax ? Config::ITEMS_MAX_SIZE : Config::ITEMS_PART_SIZE;
        if (($limit - $inCount) <= 0) {
            // лимит получения записей исчерпан
            return null;
        }
        $limit -= $inCount;

        $query = $this->getBaseQueryByConnectionId($connectionId);
        if ($this->isWithProfit()) {
            $query
                ->andWhere([
                    'BETWEEN',
                    'cdr.connect_time',
                    $from->format(DateTimeZoneHelper::DATETIME_FORMAT),
                    $to->format(DateTimeZoneHelper::DATETIME_FORMAT . '.999998')
                ])
            ;

            foreach ($query->join as $key => $join) {
                $alias = array_keys($join[1])[0];
                if (in_array($alias, [
                    'cdr2',
                    'cr2',
                ])) {
                    $condition =
                        sprintf(
                            "(%s.connect_time BETWEEN '%s' AND '%s')",
                                $alias,
                                $from->format(DateTimeZoneHelper::DATETIME_FORMAT),
                                $to->format(DateTimeZoneHelper::DATETIME_FORMAT . '.999998')
                        );
                    $query->join[$key][2] = $join[2] . ' AND ' . $condition;
                }
            }
        }

        $query
            ->andWhere([
                'BETWEEN',
                'cr.connect_time',
                $from->format(DateTimeZoneHelper::DATETIME_FORMAT),
                $to->format(DateTimeZoneHelper::DATETIME_FORMAT . '.999998')
            ]);

        if (!$query->groupBy) {
            $query->limit($limit);
        }

        $db = Helper::getDbByConnectionId($connectionId);
        $countAll = $query->count('*', $db);
        $inCount += $countAll >= $limit ? $limit : $countAll;

        if ($inCount >= $limit) {
            Yii::$app->session->setFlash('error',
                'Статистика отображается не полностью.' .
                Html::tag('br') . PHP_EOL .
                ' Сделайте ее менее детальной или сузьте временной период'
            );
        }

        return $query->createCommand($db);
    }

    /**
     * Получение данных
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    protected function getItemsBase()
    {
        // Set dateTimes
        $from = Helper::createDateTimeWithTimeZone($this->config->from, $this->config->timeZone);
        $to = Helper::createDateTimeWithTimeZone($this->config->to, $this->config->timeZone);
        $to->modify('-1 second');

        $dateArchive = Helper::getArchiveSeparationDate();
        if ($this->isWithProfit()) {
            if ($from < Helper::getCdrWithMcnCallIdSeparationDate()) {
                throw new
                \LogicException(
                    'Выберите дату, начиная с ' .
                        Helper::getCdrWithMcnCallIdSeparationDate()
                            ->format('Y-m-d H:i:s')
                );
            }

            if ($from < $dateArchive) {
                throw new
                \LogicException(
                    'Выберите дату, начиная с ' .
                        $dateArchive
                            ->format('Y-m-d H:i:s')
                );
            }
        }

        $callCount = 0;
        // разбиваем период на части
        if ($from < $dateArchive && $to > $dateArchive) {
            $query1 = $this->makeQueryCommand($from, $dateArchive, Config::CONNECTION_ARCHIVE, $callCount);
            $query2 = $this->makeQueryCommand($dateArchive, $to, Config::CONNECTION_MAIN, $callCount);
        } else {
            $connector = $from >= $dateArchive ? Config::CONNECTION_MAIN : Config::CONNECTION_ARCHIVE;
            $query1 = $this->makeQueryCommand($from, $to, $connector, $callCount);
            $query2 = null;
        }

        return
            array_merge(
                $query1->queryAll(),
                ($query2 ? $query2->queryAll() : [])
            );
    }
}