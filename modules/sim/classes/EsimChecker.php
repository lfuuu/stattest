<?php

namespace app\modules\sim\classes;

use app\exceptions\ModelValidationException;
use app\models\EventQueue;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiProfile;
use app\modules\sim\Module;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffTags;

class EsimChecker extends \app\classes\Singleton
{

    const esim_prefix = 'esim_';

    public function check(int $accountTariffId): string
    {
        $accountTariff = self::_getAccountTariff($accountTariffId);

        if ($accountTariff->iccid) {
            return 'Already ICCID added';
        }

        $tags = $accountTariff->tariffPeriod->tariff->tags;
        $esimTags = [];
        $tag = $accountTariff->tariffPeriod->tariff->tag;
        if ($tag) {
            $esimTags[] = $tag->name;
        }

        $tags = array_filter($tags, fn(TariffTags $t) => strpos(strtolower($t->tag->name), self::esim_prefix) === 0 && !in_array($t->tag->name, $esimTags));
        foreach ($tags as $tag) {
            $esimTags[] = $tag->tag->name;
        }

        if (!$esimTags) {
            return 'no eSIM tags found';
        }

        EventQueue::go(Module::EVENT_ESIM_ATTACH, [
            'client_account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->id,
            'esim_tag_names' => $esimTags,
        ]);

        return 'The following tags were found: ' . implode(', ', $esimTags);
    }

    public function attach(int $accountTariffId, array $esimTags): string
    {
        $accountTariff = self::_getAccountTariff($accountTariffId);

        if ($accountTariff->iccid) {
            return 'Already ICCID added';
        }

        $transactionA = $transactionC = null;

        try {
            foreach ($esimTags as $tagName) {

                /** @var CardStatus $cardStatus */
                $cardStatus = CardStatus::find()->where(['name' => $tagName])->one();
                if (!$cardStatus) {
                    continue;
                }

                $transactionA = AccountTariff::getDb()->beginTransaction();
                $transactionC = Card::getDb()->beginTransaction();

                $imsi = Imsi::dao()->getNextImsi($cardStatus->id, ImsiProfile::ID_S6);

                $card = $imsi->card;
                $card->client_account_id = $accountTariff->client_account_id;
                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }

                $accountTariff->iccid = $imsi->iccid;
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff);
                }
                $transactionA->commit();
                $transactionC->commit();

                return 'ICСID ' . $card . ' added successfully to accountTariff: ' . $accountTariff->id;
            }


        } catch (\Exception $e) {
            $transactionA && $transactionA->rollBack();
            $transactionC && $transactionC->rollBack();

            throw $e;
        }

        throw new \LogicException('No store found');
    }

    /**
     * @param int $accountTariffId
     * @return AccountTariff
     */
    private function _getAccountTariff(int $accountTariffId): AccountTariff
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->where(['id' => $accountTariffId])
            ->one();

        if (!$accountTariff) {
            throw new \InvalidArgumentException('AccountTariff with id: ' . $accountTariffId . ' not found');
        }

        return $accountTariff;
    }
}