<?php

namespace app\dao;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\models\NumberLog;
use app\models\voip\Registry;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Region;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
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
//                'city_id' => $registry->city_id
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
        $registryId = null;
        $lastRegistryId = null;
        $lastDidGroupSet = null;
        $lastInStock = null;

        foreach ($numbers->query() as $numberArr) {
            $number = $numberArr['number'];
            $isDidGroupSet = (bool)$numberArr['did_group_id'];
            $isInStock = ($numberArr['status'] != 'notsale');
            $registryId = $numberArr['registry_id'];

            if (!$startValue) {
                if ($registry->number_full_from < $number) {
                    $data[] = ['filling' => 'pass', 'start' => $registry->number_full_from, 'end' => $number - 1];
                }

                $startValue = $number;
                $lastValue = $number;
                $lastDidGroupSet = $isDidGroupSet;
                $lastInStock = $isInStock;
                $lastRegistryId = $registryId;
                continue;
            }

            if (($number - $lastValue - 1) > 0 || $lastDidGroupSet != $isDidGroupSet || $isInStock != $lastInStock) {
                $data[] = [
                    'filling' => $lastInStock ? 'instock' : 'fill',
                    'is_with_did_group' => $lastDidGroupSet,
                    'start' => $startValue, 'end' => $lastValue, 'registry_id' => $registryId, 'is_alien_registry' => $registry->id != $registryId, 'isDidGroupSet' => $numberArr,
                ];
                if (($number - $lastValue - 1) > 0) {
                    $data[] = ['filling' => 'pass', 'start' => $lastValue + 1, 'end' => $number - 1];
                }
                $startValue = $number;
                $lastDidGroupSet = $isDidGroupSet;
                $lastInStock = $isInStock;
            } elseif ($lastRegistryId != $registryId) {
                $data[] = [
                    'filling' => $lastInStock ? 'instock' : 'fill',
                    'is_with_did_group' => $isDidGroupSet,
                    'start' => $startValue, 'end' => $lastValue, 'registry_id' => $lastRegistryId, 'is_alien_registry' => $registry->id != $lastRegistryId];
                $startValue = $number;
                $lastDidGroupSet = $isDidGroupSet;
                $lastInStock = $isInStock;
            }

            $lastValue = $number;
            $lastRegistryId = $registryId;
        }

        if ($startValue) {
            $data[] = [
                'filling' => $lastInStock ? 'instock' : 'fill',
                'is_with_did_group' => $lastDidGroupSet, 'start' => $startValue, 'end' => $lastValue, 'registry_id' => $registryId, 'is_alien_registry' => $registry->id != $registryId];
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

//        if (!$this->_didGroups) {
//            throw new InvalidConfigException(
//                \Yii::t(
//                    'number',
//                    'No DID groups found for {country} {ndcType} {city}',
//                    [
//                        'country' => $registry->country->name,
//                        'ndcType' => $registry->ndcType->name,
//                        'city' => ($registry->city_id ? $registry->city->name : '')
//                    ]
//                )
//            );
//        }

        $filledCount = 0;
        foreach ($registry->getPassMap() as $part) {
            if ($part['filling'] == 'pass') {
                for ($i = $part['start']; $i <= $part['end']; $i++) {
                    $this->addNumber($registry, $i);
                }
            }
        }
    }

    /**
     * Добавление номера
     *
     * @param Registry $registry
     * @param string $addNumber
     * @return Number
     * @throws ModelValidationException
     * @throws InvalidConfigException
     */
    public function addNumber(Registry $registry, $addNumber)
    {
        if ($registry->isSourcePotability() || $registry->ndc_type_id == NdcType::ID_FREEPHONE) {
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
        $number->original_beauty_level = $beautyLevel;
        $number->registry_id = $registry->id;
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
        $number->source = $registry->source;
        $registry->fmc_trunk_id && $number->fmc_trunk_id = $registry->fmc_trunk_id;
        $registry->mvno_trunk_id && $number->mvno_trunk_id = $registry->mvno_trunk_id;
        $registry->mvno_partner_id && $number->mvno_partner_id = $registry->mvno_partner_id;
        $registry->nnp_operator_id && $number->nnp_operator_id = $registry->nnp_operator_id;

        $numberInfo = Number::getNnpInfo($number->number);

        $number->nnp_region_id = $numberInfo['nnp_region_id'] ?? 0;
        $number->nnp_city_id = $numberInfo['nnp_city_id'] ?? 0;
        $number->nnp_operator_id = $numberInfo['nnp_operator_id'] ?? 0;

        $didGroupId = DidGroup::dao()->getIdByNumber($number);

        $didGroup = null;
        if ($didGroupId) {
            $didGroup = DidGroup::findOne(['id' => $didGroupId]);
        }

        $numberParams = [
            'number' => $addNumber,
            'beautyLevel' => \Yii::t('app', DidGroup::$beautyLevelNames[$beautyLevel])
        ];

//        if (!$didGroup) {
//            throw new InvalidConfigException(
//                \Yii::t(
//                    'number',
//                    'For the number {number} ({ndc_type}) with beauty: "{beautyLevel}" no DID group was found',
//                    $numberParams + ['ndc_type' => $registry->ndcType->name]
//                )
//            );
//        }

        if ($didGroup && $didGroup->ndc_type_id != $registry->ndc_type_id) {
            throw new InvalidConfigException(
                \Yii::t(
                    'app',
                    'Number type {number} ("{beautyLevel}") in the DID-group (id: {didId}) and in the registry do not match',
                    $numberParams + ['didId' => $didGroup->id]
                )
            );
        }

        if ($didGroup) {
            $number->did_group_id = $didGroup->id;
        }

        if (!$number->save()) {
            throw new ModelValidationException($number);
        }

        Number::dao()->log($number, NumberLog::ACTION_CREATE, "Y");

        if ($registry->isSourcePotability()) {
            Number::dao()->startReserve($number, ClientAccount::findOne(['id' => $registry->account_id]));
        }

        $transaction->commit();

        return $number;
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
                ->where(['registry_id' => $registry->id])
                ->select([
                    'full_status' => new Expression("if(did_group_id is null, 'without_did_group', status)"),
                    'count' => new Expression('count(*)')
                ])
                ->groupBy('full_status')
                ->all(),
            'full_status',
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
                     ->andWhere(['NOT', ['did_group_id' => null]])
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

    /**
     * Привязать реестр к неприкрепленным номерам, входящие в диапазон этого реестра
     *
     * @param Registry $registry
     * @throws \Exception
     */
    public function attachNumbers(Registry $registry)
    {
        $numbersWithoutRegistry = array_column(
            array_filter($registry->getPassMap(), function ($item) {
                return $item['filling'] == 'fill' && !$item['registry_id'];
            }), 'end', 'start');

        $transaction = Number::getDb()->beginTransaction();
        try {
            foreach ($numbersWithoutRegistry as $start => $end) {
                $query = Number::find()
                    ->where([
                        'between',
                        'number',
                        $start,
                        $end
                    ])
                    ->andWhere([
                        'registry_id' => null,
                        'city_id' => $registry->city_id
                    ]);

                /** @var Number $number */
                foreach ($query->each() as $number) {
                    $number->registry_id = $registry->id;
                    if (!$number->save()) {
                        throw new ModelValidationException($number);
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    public function setDidGroup(Registry $registry)
    {
        $countWitoutDidGroup = 0;
        /** @var Number $number */
        foreach (Number::find()
                     ->where([
                         'registry_id' => $registry->id,
                         'did_group_id' => null,
                         'status' => Number::STATUS_NOTSALE
                     ])->all() as $number) {
            $didGroupId = DidGroup::dao()->getIdByNumber($number);
            if ($didGroupId) {
                $number->did_group_id = $didGroupId;
                $number->save();
            } else {
                $countWitoutDidGroup++;
            }
        }

        if ($countWitoutDidGroup) {
            throw new \LogicException('Не найдена DID-группа для ' .$countWitoutDidGroup . ' номер(ов).');
        }
    }

    public function addPortedNumber($accountId, $numberStr)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        $info = '';
        try {
            // add number to registry
            $number = Number::findOne(['number' => $numberStr]);
            if (!$number) {
                $numberInfo = Number::getNnpInfo($numberStr);

                if (!$numberInfo) {
                    throw new \LogicException('numberInfo empty');
                }

                $nnpRegionId = $numberInfo['nnp_region_id'] ?? 0;

                if (!$nnpRegionId) {
                    throw new \LogicException('NNP Region empty');
                }

                if (!isset(Region::nnpRegionToStatCity[$nnpRegionId])) {
                    throw new \LogicException('ННП Регион (' . $nnpRegionId . ') не привязан к городу');
                }

                $cityId = Region::nnpRegionToStatCity[$nnpRegionId];

                $registry = new Registry();
                $registry->id = null;
                $registry->ndc_type_id = NdcType::ID_MOBILE;
                $registry->city_id = $cityId;
                $registry->country_id = Country::RUSSIA;
                $registry->account_id = ClientAccount::ID_PORTED;
                $registry->source = VoipRegistrySourceEnum::PORTABILITY_NOT_FOR_SALE;
                $registry->ndc = substr($numberStr, 1, 3);
                $registry->mvno_partner_id = 5;
                $registry->mvno_trunk_id = 1231;

                $number = Registry::dao()->addNumber($registry, $numberStr);
                $info .= 'Number created';

                $number->client_id = $accountId;
                $number->status = Number::STATUS_RELEASED;

                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }
                Number::dao()->log($number, NumberLog::ACTION_MOVE_TO_RELEASED, "Y");
                $info .= PHP_EOL . 'Number moved to released status';
            }

            $transaction->commit();

            return $info;
        } catch (\Exception $e) {
            $transaction && $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * При фактическом переезде номера к МСН Телеком у регулятора
     * создаём услугу
     *
     * @param string $numberStr
     * @return string
     * @throws ModelValidationException
     */
    public function createAccountTariffForPortedNumber($numberStr)
    {
        $info = '';

        $number = Number::findOne(['number' => $numberStr]);
        if (!$number) {
            $info .= 'Number not found ' . $numberStr;

            return $info;
        }

        if (!AccountTariff::find()->where(['voip_number' => $number->number])->exists()) {
            $accountId = $number->client_id;

            Number::dao()->toInstock($number);
            $apiKey = \Yii::$app->params['API_SECURE_KEY'];
            $siteUrl = \Yii::$app->params['SITE_URL'];
            $queryString = http_build_query([
                'client_account_id' => $accountId,
                'service_type_id' => ServiceType::ID_VOIP,
                'tariff_period_id' => TariffPeriod::PORTED_ID,
                'voip_number' => $number->number,
                'is_async' => 0,
                'is_create_user' => 1,
            ]);
            $command = "curl -s -X PUT --header 'Content-Type: application/x-www-form-urlencoded' --header 'Accept: application/json' --header 'Authorization: Bearer {$apiKey}' -d '{$queryString}' '{$siteUrl}api/internal/uu/add-account-tariff'";

            @ob_start();
            system($command);
            $result = ob_get_clean();

            $result = json_decode($result, true);
            if (!$result) {
                sleep(3);
                $number->refresh();

                if ($number->status == Number::STATUS_INSTOCK) {
                    Number::dao()->startNotSell($number);
                }
            } else {
                if (isset($result['status']) && $result['status'] == 'ERROR') {
                    Number::dao()->startReserve($number, $number->clientAccount);
                }
            }
            $info .= 'Service created' . PHP_EOL . json_encode($result) . PHP_EOL;
        } else {
            $info .= 'Service already exists for number' . $number->number;
        }

        return $info;
    }
}
