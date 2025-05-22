<?php

namespace app\modules\sim\commands;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\HandlerLogger;
use app\exceptions\ModelValidationException;
use app\models\Country;
use app\models\ModelLifeLog;
use app\models\Number;
use app\models\Region;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\uu\behaviors\AccountTariffCheckHlr;
use app\modules\uu\models\AccountTariff;
use yii\console\Controller;
use yii\db\Expression;

class CardController extends Controller
{
    /**
     * Открепление карт с виртуальных складов
     *
     * @return void
     */
    public function actionClear($filterRegionId = null)
    {
        echo PHP_EOL . '(=) Start: ' . date('r');
        foreach (Region::getList(false, Country::RUSSIA, Region::TYPE_NODE) as $regionId => $regionName) {
            if ($filterRegionId && $filterRegionId != $regionId) {
                continue;
            }

            echo PHP_EOL . sprintf("(=) Region: %s (id: %s)", $regionName, $regionId);

            /** @var CardStatus $storage */
            $storage = CardStatus::find()->isVirt()->regionId($regionId)->one();

            if (!$storage) {
                echo PHP_EOL . '(-) Склад не найден';
                continue;
            }
            echo PHP_EOL . sprintf("(+) Склад: %s (id: %s)", $storage->name, $storage->id);

            $cardQuery = Card::find()
                ->where([
                    'region_id' => $regionId,
                    'is_active' => 1,
                    'status_id' => $storage->id,
                ])
                ->andWhere(['NOT', ['client_account_id' => null]])
                ->with('imsies');

//            $cardQuery->andWhere(['iccid' => 8970137621000019025]);

            $indent = '         ';

            /** @var Card $card */
            foreach ($cardQuery->each() as $card) {
                $log = PHP_EOL . '    ' . sprintf('ICCID: %s', $card->iccid);

                $isNeedClear = true;
                foreach ($card->imsies as $imsi) {
                    $log .= PHP_EOL . $indent . sprintf('IMSI: %s, phone: %s', $imsi->imsi, $imsi->msisdn ?: '---');
                    if ($imsi->msisdn) {
                        $isNeedClear = false;
                    }
                }

                if ($isNeedClear) {
                    $result = Card::dao()->actionSetUnLink([$card->iccid], false);
                    if ($result) {
                        $log .= PHP_EOL . $indent . '(+) Отсоединено от ЛС';
                        echo $log;
                    }
                    continue;
                }
            }
        }

        echo PHP_EOL . '(=) Stop: ' . date('r');
        echo PHP_EOL;
    }

    public function actionGetNextImsi($statusId)
    {
        $imsi = Imsi::dao()->getNextImsi($statusId);
        print_r($imsi->getAttributes());
    }

    public function actionAssignImsiIfNotEntered($filterRegionId = null)
    {
        foreach (Region::getList(false, Country::RUSSIA, Region::TYPE_NODE) as $regionId => $regionName) {
            if ($filterRegionId && $filterRegionId != $regionId) {
                continue;
            }

            echo PHP_EOL . sprintf("(=) Region: %s (id: %s)", $regionName, $regionId);

            try {
                $this->_assignImsiIfNotEntered($regionId);
            }catch (\Exception $e) {
                echo PHP_EOL . '(?) ERROR: ' . $e->getMessage();
                continue;
            }

        }
    }

    public function _assignImsiIfNotEntered($regionId)
    {
        $numberQuery = Number::find()->where([
            'region' => $regionId,
            'status' => Number::STATUS_ACTIVE_COMMERCIAL,
            'imsi' => null,
            'ndc_type_id' => NdcType::ID_MOBILE,
            'source' => VoipRegistrySourceEnum::REGULATOR,
        ]);

        /** @var Number $number */
        foreach ($numberQuery->each() as $number) {
            echo PHP_EOL . $number;
            /** @var AccountTariff $accountTariff */
            $accountTariff = AccountTariff::find()->where(['voip_number' => $number->number])->andWhere(['not', ['tariff_period_id' => null]])->one();

            if (!$accountTariff) {
                echo PHP_EOL . '(?) ' . sprintf('Для номера %s не найдена активная услуга', $number->number);
                continue;
            }

            echo ' -> ' ;

            HandlerLogger::me()->clear('set_imsi');

            $iccid = null;
            try {
                $iccid = AccountTariffCheckHlr::reservImsi([
                    'account_tariff_id' => $accountTariff->id,
                ]);

                HandlerLogger::me()->add((string)$iccid, 'set_imsi');
            } catch (\LogicException $e) {
                echo ' Error: ' . $e->getMessage() ;
            }

            if ($iccid) {
                ModelLifeLog::log('service', $accountTariff->id, ModelLifeLog::DO_UPDATE);
            }

            echo implode(' -> ', HandlerLogger::me()->get('set_imsi')) . ' ';
        }

        echo PHP_EOL;
    }

    public function actionFix()
    {
        $imsies = Imsi::find()->where(new Expression("msisdn::text like '749%'"))->all();
        /** @var Imsi $imsi */
        foreach ($imsies as $imsi) {
            echo PHP_EOL . $imsi->msisdn . ' -> ' . $imsi->imsi;
            sleep(1);
            $imsi->msisdn = null;
            if (!$imsi->save()) {
                throw new ModelValidationException($imsi);
            }
        }
    }
}
