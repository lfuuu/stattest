<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\ReturnFormatted;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\billing\Trunk;
use app\models\City;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\UsageVoip;
use Yii;


class VoipController extends BaseController
{
    /**
     * Вернуть массив типов номера в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetNumberTypes($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $numberTypes = Tariff::getVoipTypesByCountryId($countryId, $isWithEmpty);
        ReturnFormatted::me()->returnFormattedValues($numberTypes, $format);
    }

    /**
     * Вернуть массив городов в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetCities($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $numberTypes = City::getList($isWithEmpty, $countryId);
        ReturnFormatted::me()->returnFormattedValues($numberTypes, $format);
    }

    /**
     * Вернуть массив красивости номера в зависимости от города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $cityId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetDidGroups($cityId = null, $isWithEmpty = false, $format = null)
    {
        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $numberTypes = DidGroup::getList($isWithEmpty, $cityId);
        ReturnFormatted::me()->returnFormattedValues($numberTypes, $format);
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

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
        if ($mask &&
            ($mask = strtr($mask, ['.' => '_', '*' => '%'])) &&
            preg_match('/^[\d_%]+$/', $mask)
        ) {
            $numbers->setNumberLike($mask);
        }

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
     * @param int $isWithEmpty
     * @param string $format
     * @param int $statusId
     * @param int $isPostpaid
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
            $isWithEmpty,
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
     * @param bool $isWithEmpty
     */
    public function actionGetTrunks($regionId = null, $format = null, $isWithEmpty = true)
    {
        $trunks = Trunk::getList($regionId, $isWithEmpty);
        ReturnFormatted::me()->returnFormattedValues($trunks, $format);
    }
}