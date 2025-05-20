<?php

namespace app\modules\sim\commands;

use app\models\Country;
use app\models\Number;
use app\models\Region;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\uu\behaviors\AccountTariffCheckHlr;
use app\modules\uu\models\AccountTariff;
use InvalidArgumentException;
use yii\console\Controller;

class CardController extends Controller
{
    /**
     * Открепление карт с виртуальных складов
     *
     * @return void
     */
    public function actionClear($regionId = null)
    {
        foreach (Region::getList(false, Country::RUSSIA, Region::TYPE_NODE) as $regionId => $regionName) {
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

        echo PHP_EOL;
    }

    public function actionGetNextImsi($statusId)
    {
        $imsi = Imsi::dao()->getNextImsi($statusId);
        print_r($imsi->getAttributes());
    }

    public function actionEnterImsiIfNotEntered($regionId)
    {
        /** @var CardStatus $cardStatus */
        $cardStatus = CardStatus::find()->isVirt()->regionId($regionId)->one();
        if (!$cardStatus) {
            throw new InvalidArgumentException(sprintf('Для regionId: %s не найден склад с виртуальными картами', $regionId));
        }

        $numberQuery = Number::find()->where(['region' => $regionId, 'status' => Number::STATUS_ACTIVE_COMMERCIAL, 'imsi' => null]);

        /** @var Number $number */
        foreach ($numberQuery->each() as $number) {
            echo PHP_EOL . $number;
            /** @var AccountTariff $accountTariff */
            $accountTariff = AccountTariff::find()->where(['voip_number' => $number->number])->andWhere(['not', ['tariff_period_id' => null]])->one();

            if (!$accountTariff) {
                echo PHP_EOL . '(?) ' . sprintf('Для номера %s не найдена активная услуга', $number->number);
                continue;
            }

            $iccid = AccountTariffCheckHlr::reservImsi([
                'account_tariff_id' => $accountTariff->id,
                'voip_numbers_warehouse_status' => $cardStatus->id,
            ]);

            echo ' -> ' . $iccid;
        }

        echo PHP_EOL;
    }
}
