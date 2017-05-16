<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\ReturnFormatted;
use app\models\billing\Trunk;
use app\models\City;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\UsageVoip;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;


class VoipController extends BaseController
{
    /**
     * Вернуть массив типов номера в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int|bool $isWithEmpty
     * @param string $format
     */
    public function actionGetNdcTypes($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $ndcTypes = Tariff::getVoipTypesByCountryId($countryId, (int)$isWithEmpty);
        ReturnFormatted::me()->returnFormattedValues($ndcTypes, $format);
    }

    /**
     * Вернуть массив городов в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int|bool $isWithEmpty
     * @param string $format
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
     * @param string $numberType
     * @return string
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
        $numberType = ''
    ) {
        $numbers = new FreeNumberFilter;

        switch ($numberType) {
            case Tariff::NUMBER_TYPE_NUMBER:
                $numbers->getNumbers();
                break;
            case Tariff::NUMBER_TYPE_7800:
                $numbers->getNumbers7800();
                break;
            case Tariff::NUMBER_TYPE_LINE:
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
                break;
            default:
                throw new \InvalidArgumentException('Wrong numberType');
        }

        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $numbers->setCity($cityId);
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
     */
    public function actionGetTrunks($regionId = null, $format = null, $isWithEmpty = true)
    {
        $trunks = Trunk::dao()->getList(['serverIds' => $regionId], (int)$isWithEmpty);
        ReturnFormatted::me()->returnFormattedValues($trunks, $format);
    }
}