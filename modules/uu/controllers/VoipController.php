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
use app\modules\sim\models\Card;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use yii\db\Expression;
use yii\web\Response;


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
        $cities = City::getList((int)$isWithEmpty, $countryId);
        ReturnFormatted::me()->returnFormattedValues($cities, $format);
    }

    /**
     * Вернуть массив красивости номера в зависимости от страны/города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param int $cityId Не указан - не фильтровать. Больше 0 - если  есть такая красивость/служебность у города, то брать ее, иначе от страны. Меньше 0 - только для страны без города.
     * @param int $ndcTypeId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetDidGroups($countryId, $cityId, $ndcTypeId = null, $isWithEmpty = false, $format = null)
    {
        $didGroups = DidGroup::getList((int)$isWithEmpty, $countryId, $cityId, $ndcTypeId);
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
        $numbers = new FreeNumberFilter;
        $numbers->setCountry($countryId);
        $cityId && $numbers->setCity($cityId);

        $operatorAccounts = ClientAccount::getListWithContragent(
            (int)$isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
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
        $limit = FreeNumberFilter::LIMIT,
        $ndcTypeId = ''
    )
    {
        $numbers = new FreeNumberFilter;

        switch ($ndcTypeId) {
            case NdcType::ID_MOBILE:
//                $warehouseStatusId && $numbers->setWarehouseStatus($warehouseStatusId);
                break;
            case NdcType::ID_MCN_LINE:
                // "линия без номера"
                $number = UsageVoip::dao()->getNextLineNumber();
                return Html::checkbox(
                    'AccountTariffVoip[voip_numbers][]',
                    true,
                    [
                        'value' => $number,
                        'label' => $number,
                        'class' => 'disabled',
                    ]
                );
                break;
        }

        $numbers->setCountry($countryId);
        $cityId && $numbers->setCity($cityId);
        $ndcTypeId && $numbers->setNdcType($ndcTypeId);
        $didGroupId && $numbers->setDidGroup($didGroupId);
        $operatorAccountId && $numbers->setOperatorAccount($operatorAccountId);
        $mask && $numbers->setNumberLike($mask);
        $orderByField && $orderByType && $numbers->orderBy([$orderByField => (int)$orderByType]);
        $limit && $numbers->setLimit($limit);

        return $this->renderPartial(
            'getFreeNumbers',
            [
                'numbers' => $numbers->result(),
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
     * @param int $ndcTypeId
     * @param int|bool $isWithEmpty
     * @param string $format
     * @param int $isIncludeVat
     * @param int $organizationId
     * @throws \InvalidArgumentException
     * @throws \yii\base\ExitException
     */
    public function actionGetTariffPeriods(
        $serviceTypeId,
        $currency,
        $countryId,
        $cityId = null,
        $ndcTypeId = null,
        $isWithEmpty = 0,
        $format = null,
        $isIncludeVat = null,
        $organizationId = null)
    {
        $clientAccount = $this->_getCurrentClientAccount();

        $tariffPeriods = TariffPeriod::getList(
            $defaultTariffPeriodId,
            $serviceTypeId,
            $currency,
            $clientAccount ? $clientAccount->getUuCountryId() : null,
            $countryId, // @todo переименовать в voipCountryId
            $cityId,
            (int)$isWithEmpty,
            $isWithNullAndNotNull = false,
            $statusId = null,
            $isIncludeVat,
            $organizationId,
            $ndcTypeId
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
     * Вернуть массив Узлов из калиграфии
     * Используется для динамической подгрузки select2 или selectbox при редктировании (создании) транка
     *
     * @param int $regionId
     * @param string $format
     * @throws \yii\base\ExitException
     */
    public function actionGetCalligrapherNodes($regionId, $format = null)
    {
        ReturnFormatted::me()->returnFormattedValues(AccountTariff::getCalligrapherNodeList($regionId), $format);
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
            ->one(NumberRange::getDbSlave());

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
            'city_source' => $numberRange->city_source,
            'region' => $this->_getIdNameRecord($numberRange->region),
            'city' => $this->_getIdNameRecord($numberRange->city),
            'is_mob' => (int)$numberRange->is_mob,
            'is_active' => (int)$numberRange->is_active,
        ];
    }

    public function actionIccidList($q = null, $id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $out = ['results' => ['id' => '', 'text' => '']];

        if ($id) {
            $out['results'] = ['id' => (string)$id, 'text' => (string)$id];
        } elseif ($q) {
            $iccidExp = new Expression('iccid::text');
            $query = Card::find()->select(['id' => $iccidExp, 'text' => $iccidExp])
                ->where(new Expression('iccid::text like :q', ['q' => '%' . $q . '%']))
                ->andWhere(['client_account_id' => null])
                ->limit(20);
            $command = $query->createCommand(Card::getDb());
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }
}