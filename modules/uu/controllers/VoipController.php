<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\ReturnFormatted;
use app\controllers\api\internal\IdNameRecordTrait;
use app\models\billing\Trunk;
use app\models\City;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\uu\models\TariffPeriod;
use yii\db\Expression;


class VoipController extends BaseController
{
    use IdNameRecordTrait;

    /**
     * Вернуть массив городов в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetCities($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $ndcTypes = City::getList((int)$isWithEmpty, $countryId);
        ReturnFormatted::me()->returnFormattedValues($ndcTypes, $format);
    }

    /**
     * Вернуть массив красивости номера в зависимости от города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $cityId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetDidGroups($cityId = null, $isWithEmpty = false, $format = null)
    {
        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $ndcTypes = DidGroup::getList((int)$isWithEmpty, $cityId);
        ReturnFormatted::me()->returnFormattedValues($ndcTypes, $format);
    }

    /**
     * Вернуть массив свободных номеров по городу и красивости номера
     *
     * @param int $cityId
     * @param int $didGroupId
     * @param int $rowClass
     * @param string $orderByField
     * @param string $orderByType
     * @param string $mask
     * @param int $limit
     * @param string $ndcTypeId
     * @return string
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     */
    public function actionGetFreeNumbers(
        $cityId = null,
        $didGroupId = null,
        $rowClass = 6,
        $orderByField = null,
        $orderByType = null,
        $mask = '',
        $limit = 0,
        $ndcTypeId = ''
    ) {
        $numbers = new FreeNumberFilter;

        if ($ndcTypeId == NdcType::ID_LINE) {
            // "линия без номера" - это ненастоящий тип NDC
            $number = UsageVoip::dao()->getNextLineNumber();
            return Html::checkbox(
                'numberIds[]',
                true,
                [
                    'value' => $number,
                    'label' => $number,
                    'disabled' => 'disabled',
                ]
            );
        }

        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $numbers->setCity($cityId);
        $ndcTypeId && $numbers->setNdcType($ndcTypeId);
        $didGroupId && $numbers->setDidGroup($didGroupId);
        $mask && $numbers->setNumberLike($mask);

        $orderByField && $orderByType && $numbers->orderBy([$orderByField => (int)$orderByType]);
        $limit = (int)$limit;

        return $this->renderPartial(
            'getFreeNumbers',
            [
                'numbers' => $numbers->result($limit ?: 100),
                'rowClass' => $rowClass,
            ]
        );
    }

    /**
     * Вернуть массив тарифов в зависимости от города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $serviceTypeId
     * @param string $currency
     * @param int $cityId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @param int $statusId
     * @param int $isPostpaid
     * @throws \InvalidArgumentException
     */
    public function actionGetTariffPeriods($serviceTypeId, $currency, $cityId = null, $isWithEmpty = 0, $format = null, $statusId = null, $isPostpaid = null)
    {
        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $tariffPeriods = TariffPeriod::getList(
            $defaultTariffPeriodId,
            $serviceTypeId,
            $currency,
            $cityId,
            (int)$isWithEmpty,
            $isWithNullAndNotNull = false,
            $statusId,
            $isPostpaid
        );

        ReturnFormatted::me()->returnFormattedValues($tariffPeriods, $format, $defaultTariffPeriodId);
    }

    /**
     * Вернуть массив транков в зависимости от региона (сервера)
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $regionId
     * @param string $format
     * @param int|bool $isWithEmpty
     * @throws \yii\base\ExitException
     */
    public function actionGetTrunks($regionId = null, $format = null, $isWithEmpty = true)
    {
        $trunks = Trunk::dao()->getList(['serverIds' => $regionId], (int)$isWithEmpty);
        ReturnFormatted::me()->returnFormattedValues($trunks, $format);
    }

    /**
     * Вернуть ННП-информацию о номере
     *
     * @param string $number
     * @throws \yii\base\ExitException
     */
    public function actionGetNumberRange($number)
    {
        $number = str_replace([' ', '_'], '', $number);

        /** @var NumberRange $numberRange */
        $numberRange = NumberRange::find()
            ->andWhere(['is_active' => true])
            ->andWhere(['<=', 'full_number_from', $number])
            ->andWhere(['>=', 'full_number_to', $number])
            ->orderBy(new Expression('ndc IS NOT NULL DESC'))// чтобы большой диапазон по всей стране типа 0000-9999 был в конце
            ->one();

        $returnArray = [];
        if ($numberRange) {
            $returnArray = $this->_getNumberRangeRecord($numberRange);
        }

        ReturnFormatted::me()->returnFormattedValues($returnArray, ReturnFormatted::FORMAT_JSON);
    }

    /**
     * Копипаст из NnpController
     *
     * @param NumberRange $numberRange
     * @return array
     */
    private function _getNumberRangeRecord(NumberRange $numberRange)
    {
        return [
            'id' => $numberRange->id,
            'country' => $this->_getIdNameRecord($numberRange->country, 'code'),
            'ndc' => $numberRange->ndc,
            'full_number_from' => $numberRange->full_number_from,
            'full_number_to' => $numberRange->full_number_to,
            'number_from' => $numberRange->number_from,
            'number_to' => $numberRange->number_to,
            'operator_source' => $numberRange->operator_source,
            'operator' => $this->_getIdNameRecord($numberRange->operator),
            'region_source' => $numberRange->region_source,
            'region' => $this->_getIdNameRecord($numberRange->region),
            'city' => $this->_getIdNameRecord($numberRange->city),
            'is_mob' => (int)$numberRange->is_mob,
            'is_active' => (int)$numberRange->is_active,
        ];
    }
}