<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;
use Yii;
use yii\console\Controller;

class CardController extends Controller
{
    public function actionImportBunchOfMultiImsi($filepath)
    {
        $filepath = Yii::getAlias("@app/$filepath");
        if (($handle = fopen($filepath, 'r')) !== false) {
            $i = 0;
            while (($data = fgetcsv($handle)) !== false) {
                // Пропуск заголовков файла
                if ($i++ === 0) {
                    continue;
                }
                try {
                    // Удаление последнего символа, рассчитанного алгоритмом Луна
                    $iccid = substr($data[0], 0, strlen($data[0]) - 1);
                    // Получение или создание объекта модели Card
                    $card = Card::findOne(['iccid' => $iccid]);
                    if (!$card) {
                        $card = new Card;
                        $card->iccid = $iccid;
                        $card->is_active = true;
                        $card->status_id = CardStatus::ID_DEFAULT;
                        if (!$card->save()) {
                            throw new ModelValidationException($card);
                        }
                        echo sprintf('[%d] Created Card with iccid - %s. ', $i, $iccid) . PHP_EOL;
                    }
                    // Получение всех imsies, привязанных к сим-карте по iccid
                    $imsies = Imsi::find()
                        ->select('imsi')
                        ->where(['iccid' => $card->iccid])
                        ->asArray()
                        ->column();
                    // Перебираем импортируемые imsi
                    for ($j = 1; $j < 4; $j++) {
                        // Такое imsi уже существует, пропускаем итерацию
                        if (in_array((int)$data[$j], $imsies)) {
                            continue;
                        }
                        // Создание объекта imsi, привязанного к сим-карте
                        try {
                            $imsi = new Imsi;
                            $imsi->imsi = $data[$j];
                            $imsi->iccid = $card->iccid;
                            $imsi->is_active = true;
                            $imsi->status_id = ImsiStatus::ID_DEFAULT;
                            $imsi->partner_id = $j === 1 ? 1  : 2;
                            if (!$imsi->save()) {
                                throw new ModelValidationException($imsi);
                            }
                            echo sprintf('Created Imsi - %s by iccid: %s; ', $imsi->imsi, $imsi->iccid) . PHP_EOL;
                        } catch (ModelValidationException $e2) {
                            echo $e2->getMessage() . PHP_EOL;
                        }
                    }
                    echo  PHP_EOL;
                } catch (ModelValidationException $e1) {
                    echo $e1->getMessage() . PHP_EOL;
                }
            }
            fclose($handle);
        }
    }
}