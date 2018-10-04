<?php

namespace app\commands\convert;

use ActiveRecord\RecordNotFound;
use app\exceptions\ModelValidationException;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiStatus;
use Yii;
use yii\console\Controller;

class CardController extends Controller
{
    /**
     * Дополнение IMSIes по партнеру Roamability
     *
     * @param string $filepath
     */
    public function actionImportRoamability($filepath)
    {
        // Получение партнера Roamability
        if (!$partner = ImsiPartner::findOne(['id' => ImsiPartner::ROAMABILITY])) {
            echo 'Partner Roamability not found';
            return;
        }
        // Получение дескриптора файла
        $filepath = Yii::getAlias("@app/$filepath");
        if (($handle = fopen($filepath, 'rb')) === false) {
            echo sprintf('File "%s" not found', $filepath);
            return;
        }
        $i = 0;
        while (($data = fgetcsv($handle, 0, ' ')) !== false) {
            // Пропускаем заголовок файла
            if ($i++ === 0) {
                continue;
            }
            try {
                $card = $this->_getCard($data[0]);
                // Получение всех imsies, привязанных к сим-карте по iccid
                $imsies = Imsi::find()
                    ->select('imsi')
                    ->where(['iccid' => $card->iccid])
                    ->asArray()
                    ->column();
                // Создание объекта imsi, привязанного к сим-карте
                foreach ($data as $key => $value) {
                    // Пропускаем ненужные столбцы, оставляя только те, которые принадлежат Roamability
                    if (in_array($key, [0, 1, 3, 4])) {
                        continue;
                    }
                    // Такое imsi уже существует, пропускаем итерацию
                    if (in_array($value, $imsies)) {
                        echo "IMSI $value already exists";
                        continue;
                    }
                    try {
                        $imsi = new Imsi;
                        $imsi->imsi = $value;
                        $imsi->iccid = $card->iccid;
                        $imsi->is_active = true;
                        $imsi->status_id = ImsiStatus::ID_DEFAULT;
                        $imsi->partner_id = $partner->id;
                        if (!$imsi->save()) {
                            throw new ModelValidationException($imsi);
                        }
                    } catch (\Exception $e1) {
                        echo $e1->getMessage() . PHP_EOL;
                    }
                }
            } catch (\Exception $e2) {
                echo $e2->getMessage() . PHP_EOL;
            }
        }
        fclose($handle);
    }

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
                    $card = $this->_getCard($iccid);
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
                        } catch (ModelValidationException $e1) {
                            echo $e1->getMessage() . PHP_EOL;
                        }
                    }
                } catch (ModelValidationException $e2) {
                    echo $e2->getMessage() . PHP_EOL;
                } catch (RecordNotFound $e3) {
                    echo $e3->getMessage() . PHP_EOL;
                }
            }
            fclose($handle);
        }
    }

    /**
     * @param $iccid
     * @param bool $withCreating
     * @return Card
     * @throws ModelValidationException
     */
    private function _getCard($iccid, $withCreating = true)
    {
        $card = Card::findOne(['iccid' => $iccid]);
        if (!$card) {
            if ($withCreating) {
                $card = new Card;
                $card->iccid = $iccid;
                $card->is_active = true;
                $card->status_id = CardStatus::ID_DEFAULT;
                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }
                echo sprintf('Successfully created %s with ICCID=%d', Card::class, $iccid) . PHP_EOL;
            } else {
                throw new RecordNotFound(sprintf('Couldn\'t find %s with ICCID=%d', Card::class, $iccid));
            }
        }
        return $card;
    }
}