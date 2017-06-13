<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\ReturnFormatted;
use app\classes\traits\AddClientAccountFilterTraits;
use app\controllers\api\internal\IdNameRecordTrait;
use app\models\billing\Trunk;
use app\models\City;
use app\models\ClientAccount;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use yii\db\Expression;


class VoipController extends BaseController
{
    use IdNameRecordTrait;
    use AddClientAccountFilterTraits;

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
    public function actionGetCities($countryId, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $cities = City::getList((int)$isWithEmpty, $countryId);
        ReturnFormatted::me()->returnFormattedValues($cities, $format);
    }

    /**
     * Вернуть массив красивости номера в зависимости от страны/города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int $cityId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetDidGroups($countryId, $cityId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $didGroups = DidGroup::getList((int)$isWithEmpty, $countryId, $cityId);
        ReturnFormatted::me()->returnFormattedValues($didGroups, $format);
    }

    /**
     * Вернуть массив NDC
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param null|bool $isCityDepended
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetNdcTypes($isCityDepended = null, $isWithEmpty = false, $format = null)
    {
        $ndcTypes = NdcType::getList((int)$isWithEmpty, $isWithNullAndNotNull = false, $isCityDepended);
        $isCityDepended && $ndcTypes[NdcType::ID_LINE] = 'Линия без номера';
        ReturnFormatted::me()->returnFormattedValues($ndcTypes, $format);
    }

    /**
     * Вернуть массив аккаунтов операторов в зависимости от страны/города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int $cityId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetOperatorAccounts($countryId, $cityId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $numbers = new FreeNumberFilter;
        $numbers->setCountry($countryId);
        $cityId && $numbers->setCity($cityId);

        $operatorAccounts = ClientAccount::getListTrait(
            (int)$isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'client',
            $orderBy = ['id' => SORT_ASC],
            $where = ['id' => $numbers->getDistinct('operator_account_id')]
        );
        ReturnFormatted::me()->returnFormattedValues($operatorAccounts, $format);
    }

    /**
     * Вернуть массив свободных номеров по стране/городу и красивости номера
     *
     * @param int $countryId
     * @param int $cityId
     * @param int $didGroupId
     * @param int $operatorAccountId
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
        $countryId,
        $cityId = null,
        $didGroupId = null,
        $operatorAccountId = null,
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

        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        $numbers->setCountry($countryId);
        $cityId && $numbers->setCity($cityId);
        $ndcTypeId && $numbers->setNdcType($ndcTypeId);
        $didGroupId && $numbers->setDidGroup($didGroupId);
        $operatorAccountId && $numbers->setOperatorAccount($operatorAccountId);
        $mask && $numbers->setNumberLike($mask);

        $orderByField && $orderByType && $numbers->orderBy([$orderByField => (int)$orderByType]);
        $limit = (int)$limit;

        return $this->renderPartial(
            'getFreeNumbers',
            [
                'numbers' => $numbers->result($limit ?: 100),
                'rowClass' => $rowClass,
                'clientAccount' => $this->_getCurrentClientAccount(),
            ]
        );
    }

    /**
     * Вернуть массив тарифов в зависимости от страны/города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $serviceTypeId
     * @param string $currency
     * @param int $countryId
     * @param int $cityId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @param int $isPostpaid
     * @param int $didGroupId
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetTariffPeriods($serviceTypeId, $currency, $countryId, $cityId = null, $isWithEmpty = 0, $format = null, $isPostpaid = null, $didGroupId = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }

        if (!$didGroupId) {
            throw new \InvalidArgumentException('Wrong didGroupId');
        }

        $didGroup = DidGroup::findOne(['id' => $didGroupId]);
        if (!$didGroup) {
            throw new \InvalidArgumentException('Не найдена DID-группа ' . $didGroupId);
        }

        if ($serviceTypeId == ServiceType::ID_VOIP) {
            $clientAccount = $this->_getCurrentClientAccount();
            $priceLevel = $clientAccount ? $clientAccount->price_level : ClientAccount::DEFAULT_PRICE_LEVEL;
            $statusId = $didGroup->{'tariff_status_main' . $priceLevel};
        } else {
            $statusId = null;
        }

        $tariffPeriods = TariffPeriod::getList(
            $defaultTariffPeriodId,
            $serviceTypeId,
            $currency,
            $countryId,
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