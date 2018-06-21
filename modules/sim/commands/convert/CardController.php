<?php

namespace app\modules\sim\commands\convert;

use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use yii\console\Controller;

class CardController extends Controller
{
    /**
     * Синхронизация статуса склада из sim_card и imsi из sim_imsi в voip_numbers
     */
    public function actionSynchronization()
    {
        $cardQuery = Card::find();
        // Получаем список всех сим-карт
        foreach ($cardQuery->each() as $card) {
            /** @var Card $card */
            $imsies = $card->imsies;
            if (!$imsies) {
                echo sprintf('Сим-карта %s не имеет привязанных номеров', $card->iccid) . PHP_EOL;
                continue;
            }
            // Получаем все привязанные номера к сим-карте с последующим присвоением номеру в таблице voip_numbers imsi и статуса склада сим-карты
            foreach ($imsies as $imsi) {
                /** @var Imsi $imsi */
                // TODO: Список номеров, которые игнорируются при синхронизации
                if ($imsi->msisdn === null || in_array($imsi->msisdn, Number::LIST_SKIPPING)) {
                    continue;
                }
                /** @var Number $number */
                $number = Number::findOne(['number' => $imsi->msisdn]);
                if (!$number) {
                    echo "Номер {$imsi->msisdn} найден в sim_imsi, но не найден в voip_numbers" . PHP_EOL;
                    continue;
                }
                // Присваивание imsi номеру
                $number->imsi = $imsi->imsi;
                // Присваивание склада сим-карты номеру
                $number->warehouse_status_id = $card->status_id;
                if (!$number->save()) {
                    echo sprintf('Ошибка при сохранении номера %s', $card->status_id, $number->number) . PHP_EOL;
                }
            }
        }
    }
}