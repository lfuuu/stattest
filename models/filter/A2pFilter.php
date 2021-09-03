<?php

namespace app\models\filter;

use app\classes\Form;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\validators\AccountIdValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\A2pSms;
use app\models\ClientAccount;
use yii\data\ActiveDataProvider;

class A2pFilter extends Form
{
    use AddClientAccountFilterTraits;

    const dateTimeRegexp = '/^(\d{4}-\d{2}-\d{2})( \d{2}:\d{2}:\d{2})?$/';
    const dateTimeStrongRegexp = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    public $account_id;

    public $number = '';
    public $is_in_utc = 0;
    public $from_datetime;
    public $to_datetime;
    public $group_by = '';
    public $offset = -1;
    public $limit = -1;

    private $firstDayOfDate = null;
    private $lastDayOfDate = null;
    /** @var ClientAccount */
    public $clientAccount = null;

    public $isWebReport = false;

    public $allData = 0;

    public function rules()
    {
        return [
            ['account_id', 'required'],
            [['account_id', 'offset', 'limit', 'is_in_utc'], 'integer'],
            ['number', 'trim'],
            ['offset', 'default', 'value' => 0],
            ['limit', 'default', 'value' => 1000],
            ['is_in_utc', 'default', 'value' => 1],
            ['from_datetime', 'match', 'pattern' => self::dateTimeRegexp],
            ['to_datetime', 'match', 'pattern' => self::dateTimeRegexp],
            ['account_id', AccountIdValidator::class],
            ['group_by', 'in', 'range' => ['', 'none', 'year', 'month', 'day', 'hour']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'charge_time' => 'Дата/время',
            'src_number' => 'Номера А',
            'dst_number' => 'Номера В',
            'dst_route' => 'Исходящий транк',
            'cost' => 'Стоимость',
            'rate' => 'Ставка',
            'count' => 'Кол-во частей',
        ];
    }

    private function prepare()
    {
        $this->initValues();

        if (($this->from_datetime || $this->to_datetime) && (!$this->from_datetime || !$this->to_datetime)) {
            throw new \InvalidArgumentException('fields from_datetime and to_datetime must be filled');
        }

        $this->clientAccount = ClientAccount::findOne(['id' => $this->account_id]);

        $utcTz = (new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $tz = $this->is_in_utc ? $utcTz : $this->clientAccount->timezone;


        if (!preg_match(self::dateTimeStrongRegexp, $this->from_datetime)) {
            $this->from_datetime .= ' 00:00:00';
        }

        if (!preg_match(self::dateTimeStrongRegexp, $this->to_datetime)) {
            $this->to_datetime = (new \DateTimeImmutable($this->to_datetime))
                ->modify('+1 day')
                ->setTime(0, 0, 0)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        $this->firstDayOfDate = (new \DateTimeImmutable($this->from_datetime, $tz));
        $this->lastDayOfDate = (new \DateTimeImmutable($this->to_datetime, $tz));

        $diff = $this->firstDayOfDate->diff($this->lastDayOfDate);

        if (($diff->y > 0 || $diff->m > 2 || ($diff->m > 1 && $diff->d > 2)) && !$this->group_by && $this->group_by != 'hour') {
            throw new \InvalidArgumentException('DATETIME_RANGE_LIMIT', -10);
        }
    }

    private function initValues()
    {
        if (!isset($this->from_datetime)) {
            $this->from_datetime = date(DateTimeZoneHelper::DATE_FORMAT) . ' 00:00:00';
        }

        if (!isset($this->to_datetime)) {
            $this->to_datetime = date(DateTimeZoneHelper::DATE_FORMAT, strtotime('+1 day')) . ' 00:00:00';
        }

        if (!isset($this->account_id) && $this->isWebReport) {
            $this->account_id = $this->_getCurrentClientAccountId();
        }
    }

    public function search()
    {
        $this->prepare();

        $query = A2pSms::dao()->getData(
            $this->clientAccount,
            $this->firstDayOfDate,
            $this->lastDayOfDate,
            $this->offset,
            $this->limit,
            $this->group_by
        );

        if (!$this->isWebReport) {
            return $query;
        }

        $queryClone = clone $query;
        $queryClone->groupBy = $queryClone->limit = $queryClone->offset = $queryClone->orderBy = null;
        $this->allData = $queryClone->select(['count(*) as count', 'sum(abs(cost)) as cost', 'sum(count) as parts'])->createCommand(A2pSms::getDb())->queryOne();

        return new ActiveDataProvider([
            'db' => A2pSms::getDb(),
            'query' => $query,
        ]);
    }
}
