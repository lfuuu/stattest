<?php

namespace app\modules\sim\commands\convert;

use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\models\Imsi;
use yii\console\Controller;

class CardController extends Controller
{
    /**
     * Создание связей между sim_imsi и voip_numbers
     */
    public function actionCreateRelations()
    {
        $imsies = Imsi::find()
            ->where([
                'AND',
                ['IS NOT', 'msisdn', null],
                // Временно пропускаем номер 79587980598, т.к. он используется на 3-х сим-картах
                ['!=', 'msisdn', '79587980598']
            ]);

        /** @var Imsi $imsi */
        foreach ($imsies->each() as $imsi) {
            /** @var Number $number */
            $number = Number::findOne(['number' => $imsi->msisdn]);
            if (!$number) {
                echo "Номер {$imsi->msisdn} найден в sim_imsi, но не найден в voip_numbers" . PHP_EOL;
                continue;
            }

            $transaction = Number::getDb()->beginTransaction();
            try {
                $number->imsi = $imsi->imsi;
                if (!$number->save()) {
                    throw new ModelValidationException($number);
                }
                $transaction->commit();
            } catch (ModelValidationException $e) {
                echo $e->getMessage() . PHP_EOL;
                $transaction->rollBack();
            }
        }
    }
}