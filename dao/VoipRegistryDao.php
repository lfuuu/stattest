<?php
namespace app\dao;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
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
 */
class VoipRegistryDao extends Singleton
{
    private $_didGroups = [];

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
            ->andWhere(['between', 'number', $registry->number_full_from, $registry->number_full_to])
            ->count();

        return ($count == 0 ? Registry::STATUS_EMPTY : (($registry->number_full_to - $registry->number_full_from + 1) == $count ? Registry::STATUS_FULL : Registry::STATUS_PARTLY));
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
            ->where([
                'country_code' => $registry->country_id,
                'city_id' => $registry->city_id
            ])
            ->andWhere([
                'between',
                'number',
                $registry->number_full_from,
                $registry->number_full_to
            ])
            ->orderBy(['number' => SORT_ASC])
            ->createCommand();

        $data = [];
        $lastValue = null;
        $startValue = null;
        foreach ($numbers->query() as $numberArr) {
            $number = $numberArr['number'];

            if (!$startValue) {
                if ($registry->number_full_from < $number) {
                    $data[] = ['filling' => 'pass', 'start' => $registry->number_full_from, 'end' => $number - 1];
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
            if ($lastValue < $registry->number_full_to) {
                $data[] = ['filling' => 'pass', 'start' => $lastValue + 1, 'end' => $registry->number_full_to];
            }
        } else {
            $data[] = ['filling' => 'pass', 'start' => $registry->number_full_from, 'end' => $registry->number_full_to];
        }

        return $data;
    }

    /**
     * Заливка номеров
     *
     * @param Registry $registry
     * @return bool
     * @throws InvalidConfigException
     */
    public function fillNumbers(Registry $registry)
    {
        if ($registry->status == Registry::STATUS_FULL) {
            return true;
        }

        $didGroups = DidGroup::find()
            ->where(
                [
                    'AND',
                    [
                        'ndc_type_id' => $registry->ndc_type_id,
                        'is_service' => (int)$registry->isService()
                    ],
                    [
                        'OR',
                        ['city_id' => $registry->city_id],
                        [
                            'country_code' => $registry->country_id, // страна без города
                            'city_id' => null
                        ]
                    ],
                ]
            )
            ->orderBy(new Expression('COALESCE(city_id, 0) DESC')) // выбор по стране без города имеет приоритет ниже страны с городом
        ;

        if ($registry->isSourcePotability()) {
            $didGroups->andWhere(['beauty_level' => DidGroup::BEAUTY_LEVEL_STANDART]);
        }

        $this->_didGroups = $didGroups
            ->indexBy('beauty_level')
            ->all();

        if (!$this->_didGroups) {
            throw new InvalidConfigException(
                \Yii::t(
                    'number',
                    'No DID groups found for {country} {ndcType} {city}',
                    [
                        'country' => $registry->country->name,
                        'ndcType' => $registry->ndcType->name,
                        'city' => ($registry->city_id ? $registry->city->name : '')
                    ]
                )
            );
        }

        $filledCount = 0;
        foreach ($registry->getPassMap() as $part) {
            if ($part['filling'] == 'pass') {
                for ($i = $part['start']; $i <= $part['end']; $i++) {
                    $this->_addNumber($registry, $i);
                }
            }
        }
    }

    /**
     * Добавление номера
     *
     * @param Registry $registry
     * @param string $addNumber
     * @throws InvalidConfigException
     * @throws ModelValidationException
     */
    private function _addNumber(Registry $registry, $addNumber)
    {

        if ($registry->isSourcePotability()) {
            $beautyLevel = DidGroup::BEAUTY_LEVEL_STANDART;
        } else {
            $beautyLevel = NumberBeautyDao::getNumberBeautyLvl(
                $addNumber,
                $registry->city_id ? $registry->city->postfix_length : NumberBeautyDao::DEFAULT_POSTFIX_LENGTH
            );
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();

        $number = new Number;
        $number->number = $addNumber;
        $number->beauty_level = $beautyLevel;
        $number->region = $registry->city_id ? $registry->city->connection_point_id : $registry->country->default_connection_point_id;
        $number->ndc_type_id = $registry->ndc_type_id;
        $number->city_id = $registry->city_id;
        $number->status = Number::STATUS_NOTSALE;
        $number->edit_user_id = \Yii::$app->user->identity->id;
        $number->operator_account_id = $registry->account_id;
        $number->country_code = $registry->country->code;
        $number->ndc = $registry->ndc;
        $number->number_subscriber = substr($addNumber, strlen((string)$registry->country->prefix) + strlen($registry->ndc));
        $number->date_start = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $number->is_ported = (int)$registry->isSourcePotability();
        $number->is_service = (int)$registry->isService();
        $registry->fmc_trunk_id && $number->fmc_trunk_id = $registry->fmc_trunk_id;
        $registry->mvno_trunk_id && $number->mvno_trunk_id = $registry->mvno_trunk_id;

        $didGroupId = DidGroup::dao()->getIdByNumber($number);

        $didGroup = null;
        if ($didGroupId) {
            $didGroup = DidGroup::findOne(['id' => $didGroupId]);
        }

        $numberParams = [
            'number' => $addNumber,
            'beautyLevel' => \Yii::t('app', DidGroup::$beautyLevelNames[$beautyLevel])
        ];

        if (!$didGroup) {
            throw new InvalidConfigException(
                \Yii::t(
                    'number',
                    'For the number {number} ({ndc_type}) with beauty: "{beautyLevel}" no DID group was found',
                    $numberParams + ['ndc_type' => $registry->ndcType->name]
                )
            );
        }

        if ($didGroup->ndc_type_id != $registry->ndc_type_id) {
            throw new InvalidConfigException(
                \Yii::t(
                    'app',
                    'Number type {number} ("{beautyLevel}") in the DID-group (id: {didId}) and in the registry do not match',
                    $numberParams + ['didId' => $didGroup->id]
                )
            );
        }

        $number->did_group_id = $didGroup->id;

        if (!$number->save()) {
            throw new ModelValidationException($number);
        }

        Number::dao()->log($number, NumberLog::ACTION_CREATE, "Y");

        if ($registry->isSourcePotability()) {
            Number::dao()->startReserve($number, ClientAccount::findOne(['id' => $registry->account_id]));
        }

        $transaction->commit();
    }

    /**
     * @param Registry $registry
     * @return array
     */
    public function getStatusInfo(Registry $registry)
    {
        return ArrayHelper::map(
            (new Query())
            ->from(Number::tableName())
            ->where(['city_id' => $registry->city_id])
            ->andWhere(['between', 'number', $registry->number_full_from, $registry->number_full_to])
            ->select(['status' => 'status', 'count' => new Expression('count(*)')])
            ->groupBy('status')
            ->all(),
        'status',
        'count'
        );
    }

    /**
     * Передать номера в продажу
     *
     * @param Registry $registry
     */
    public function toSale(Registry $registry)
    {
        foreach (Number::find()
            ->where(['between', 'number', $registry->number_full_from, $registry->number_full_to])
            ->andWhere([
                'city_id' => $registry->city_id,
                'status' => Number::STATUS_NOTSALE
            ])->all() as $number) {
            \Yii::$app->getDb()->transaction(function ($db) use ($number) {
                $number->status = Number::STATUS_INSTOCK;
                $number->save();
                Number::dao()->log($number, NumberLog::ACTION_SALE, "Y");
            });
        }
    }
}
