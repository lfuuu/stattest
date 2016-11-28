<?php
namespace app\dao;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\DidGroup;
use app\models\Number;
use app\models\NumberLog;
use app\models\voip\Registry;
use yii\base\InvalidConfigException;
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

        $didGroups = DidGroup::find()
            ->where([
                'OR',
                'city_id = :city_id', // город
                [
                    'AND',
                    'country_code = :country_code', // страна без города
                    'city_id IS NULL'
                ]
            ])
            ->addParams([
                ':city_id' => $registry->city_id,
                ':country_code' => $registry->country_id,
            ])
            ->orderBy(new Expression('COALESCE(city_id, 0) DESC')) // выбор по стране без города имеет приоритет ниже страны с городом
        ;

        if ($registry->isSourcePotability()) {
            $didGroups->andWhere(['beauty_level' => DidGroup::BEAUTY_LEVEL_STANDART]);
        }

        $this->didGroups = $didGroups
            ->indexBy('beauty_level')
            ->all();

        if (!$this->didGroups) {
            throw new InvalidConfigException('Не найдены DID-группы для города id:' . $registry->city_id);
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

        if ($registry->isSourcePotability()) {
            $beautyLevel = DidGroup::BEAUTY_LEVEL_STANDART;
        } else {
            $beautyLevel = NumberBeautyDao::getNumberBeautyLvl($addNumber);
        }

        if (!isset($this->didGroups[$beautyLevel])) {
            throw new InvalidConfigException('Для номера ' .$addNumber . ' с красотой: "' . DidGroup::$beautyLevelNames[$beautyLevel] . '" не найдена DID-группа');
        }

        if ($this->didGroups[$beautyLevel]->number_type_id != $registry->number_type_id) {
            throw new InvalidConfigException('Тип номера ' .$addNumber . ' ("' . DidGroup::$beautyLevelNames[$beautyLevel] . '") в DID-группе (id: ' . $this->didGroups[$beautyLevel]->id . ') и в реестре не совпадают');
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();

        $number = new Number;
        $number->number = $addNumber;
        $number->beauty_level = $beautyLevel;
        $number->did_group_id = $this->didGroups[$beautyLevel]->id;
        $number->region = $registry->city->connection_point_id;
        $number->number_type = $registry->number_type_id;
        $number->city_id = $registry->city_id;
        $number->status = Number::STATUS_NOTSALE;
        $number->edit_user_id = \Yii::$app->user->identity->id;
        $number->operator_account_id = $registry->account_id;
        $number->country_code = $registry->city->country->code;
        $number->date_start = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $number->is_ported = (int)$registry->isSourcePotability();


        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_CREATE, "Y");

        if ($registry->isSourcePotability()) {
            Number::dao()->startReserve($number, ClientAccount::findOne(['id' => $registry->account_id]));
        }

        $transaction->commit();
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
        foreach(Number::find()->where([
                'AND', ['city_id' => $registry->city_id],
                ['between', 'number', $registry->number_from, $registry->number_to],
                ['status' => Number::STATUS_NOTSALE]
            ])->all() as $number) {
            \Yii::$app->getDb()->transaction(function($db) use ($number) {
                $number->status = Number::STATUS_INSTOCK;
                $number->save();
                Number::dao()->log($number, NumberLog::ACTION_SALE, "Y");
            });
        }
    }
}
