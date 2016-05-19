<?php
namespace app\dao;

use ActiveRecord\ConfigException;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\DidGroup;
use app\models\Number;
use app\models\NumberType;
use app\models\voip\Registry;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @method static VoipRegistryDao me($args = null)
 * @property
 */
class VoipRegistryDao extends Singleton
{
    private $didGroups = [];

    /**
     * Вычисляем статус записи
     *
     * @param Registry $registry
     * @return string
     */
    public function getStatus(Registry $registry)
    {
        $count = Number::find()
            ->where(['city_id' => $registry->city_id])
            ->andWhere(['between', 'number', $registry->number_from, $registry->number_to])
            ->count();

        return ($count == 0 ? Registry::STATUS_EMPTY : (($registry->number_to - $registry->number_from + 1) == $count ? Registry::STATUS_FULL : Registry::STATUS_PARTLY));
    }

    /**
     * Функция возвращает массив пропущенных и залитых номеров
     *
     * @param Registry $registry
     * @return array
     */
    public function getPassMap(Registry $registry)
    {
        $numbers = Number::find()
            ->where(['city_id' => $registry->city_id])
            ->andWhere([
                'between',
                'number',
                $registry->number_from,
                $registry->number_to
            ])
            ->orderBy(['number' => SORT_ASC])
            ->createCommand();

        $data = [];
        $lastValue = null;
        $startValue = null;
        foreach ($numbers->query() as $numberArr) {
            $number = $numberArr['number'];

            if (!$startValue) {
                if ($registry->number_from < $number) {
                    $data[] = ['filling' => 'pass', 'start' => $registry->number_from, 'end' => $number - 1];
                }
                $startValue = $number;
                $lastValue = $number;
                continue;
            }

            if (($number - $lastValue - 1) > 0) {
                $data[] = ['filling' => 'fill', 'start' => $startValue, 'end' => $lastValue];
                $data[] = ['filling' => 'pass', 'start' => $lastValue + 1, 'end' => $number - 1];
                $startValue = $number;
            }

            $lastValue = $number;
        }

        if ($startValue) {
            $data[] = ['filling' => 'fill', 'start' => $startValue, 'end' => $lastValue];
            if ($lastValue < $registry->number_to) {
                $data[] = ['filling' => 'pass', 'start' => $lastValue + 1, 'end' => $registry->number_to];
            }
        } else {
            $data[] = ['filling' => 'pass', 'start' => $registry->number_from, 'end' => $registry->number_to];
        }

        return $data;
    }

    public function fillNumbers(Registry $registry)
    {
        if ($registry->status == Registry::STATUS_FULL) {
            return true;
        }

        $this->didGroups =  DidGroup::dao()->getDidGroupMapByCityId($registry->city_id);

        if (!$this->didGroups) {
            throw new ConfigException('Не найдены DID-группы для города id:' . $registry->city_id);
        }

        $filledCount = 0;
        foreach ($registry->getPassMap() as $part) {
            if ($part['filling'] == 'pass') {
                for ($i = $part['start']; $i <= $part['end']; $i++) {
                    $filledCount++;
                    $this->addNumber($registry, $i);
                }
            }
        }
    }

    private function addNumber(Registry $registry, $addNumber) {
        $beautyLevel = NumberBeautyDao::getNumberBeautyLvl($addNumber);

        if (!isset($this->didGroups[$beautyLevel])) {
            throw new ConfigException('Для номера ' .$addNumber . ' с красотой: ' . $beautyLevel . ' не найдена DID-группа');
        }

        $number = new Number;
        $number->number = $addNumber;
        $number->beauty_level = $beautyLevel;
        $number->did_group_id = $this->didGroups[$beautyLevel];
        $number->region = $registry->city->connection_point_id;
        $number->number_type = $registry->number_type_id;
        $number->city_id = $registry->city_id;
        $number->status = Number::STATUS_NOTSELL;
        $number->edit_user_id = \Yii::$app->user->identity->id;
        $number->operator_account_id = $registry->account_id;
        $number->country_code = $registry->city->country->code;
        $number->date_start = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(\DateTime::ATOM);

        $number->save();

    }

    public function getStatusInfo(Registry $registry)
    {
        return ArrayHelper::map(
            (new Query())
            ->from(Number::tableName())
            ->where(['city_id' => $registry->city_id])
            ->andWhere(['between', 'number', $registry->number_from, $registry->number_to])
            ->select(['status' => 'status', 'count' => new Expression('count(*)')])
            ->groupBy('status')
            ->all(),
        'status',
        'count'
        );
    }

    public function toSale(Registry $registry)
    {
        Number::updateAll(['status' => Number::STATUS_INSTOCK],
            [
                'AND', ['city_id' => $registry->city_id],
                ['between', 'number', $registry->number_from, $registry->number_to],
                ['status' => Number::STATUS_NOTSELL]
            ]
        );
    }

}
