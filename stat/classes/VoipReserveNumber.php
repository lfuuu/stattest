<?php

use app\classes\api\Errors;
use app\forms\comment\ClientContractCommentForm;
use app\models\filter\FreeNumberFilter;
use app\models\Trouble;
use app\models\ClientAccount;
use app\models\TariffVoip;
use app\forms\usage\UsageVoipEditForm;

/**
 * Class VoipReserveNumber
 * Класс резерва номера за клиентом
 */
class VoipReserveNumber
{
    /**
     * Резерв списка номеров за клиентом
     *
     * @param $clientId
     * @param array $numbers
     * @return bool
     * @throws Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public static function reserveNumbers($clientId, array $numbers)
    {
        if (empty($numbers)) {
            throw new \Exception('Номера для резерва не найдены');
        }
        
        $comment = 'Reserve numbers: <br />' . PHP_EOL;

        $numbersFilter = new FreeNumberFilter();
        $numbersFilter
            ->getNumbers()
            ->setNumbers((array)$numbers);

        foreach ($numbersFilter->result(null) as $number) {
            $comment .= $number->number . ' - ' . $number->price . '<br />' . PHP_EOL;
        }

        $account = ClientAccount::findOne($clientId);
        if (!$account) {
            throw new Exception('Клиент не найден');
        }

        $c = new ClientContractCommentForm();
        $c->contract_id = $account->contract_id;
        $c->user = 'auto';
        $c->comment = $comment;
        $c->save();

        foreach ($numbers as $number) {
            $reserveInfo = self::reserve($number, $clientId, 1, null, true);

            if ($reserveInfo) {
                $trouble = Trouble::findOne([
                        "client" => $account->client,
                        "trouble_type" => "connect",
                        "service" => ""
                    ]
                );

                if ($trouble) {
                    $trouble->service = "usage_voip";
                    $trouble->service_id = $reserveInfo["usage_id"];
                    $trouble->save();
                }
            }
        }
        return true;
    }

    /**
     * Функция резерва отдельного номера. С более детальными параметрами.
     * Используется как в составе самостоятельной функции резерва номера по 2-х запросной схеме резерва номера,
     * так и при 1-запросной схемы.
     *
     * @param $number
     * @param $clientId
     * @param int $lineCount
     * @param null $tariffId
     * @param bool $isForceStart
     * @return array
     * @throws Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public static function reserve($number, $clientId, $lineCount = 1, $tariffId = null, $isForceStart = false)
    {
        global $db;

        $number = $db->escape(trim($number));

        if ($tariffId !== null) {
            $tariffId = $db->escape($tariffId);
        }

        $voipNumber = $db->GetRow("select region,city_id,did_group_id from voip_numbers where number = '".$number."'");

        if (!$voipNumber) {
            throw new \Exception("Номер не найден");
        }

        $region = $voipNumber["region"];



        $u = $db->GetValue("select id from usage_voip where 
                (  
                 cast(now() as date) between actual_from and actual_to 
                 or (actual_from > '3000-01-01' and actual_to > '3000-01-01')
                ) and E164 = '".$number."'");

        if ($u)
            throw new yii\web\BadRequestHttpException("Номер уже используется", Errors::ERROR_RESERVE_NUMBER_BUSY);


        $client = ClientAccount::findOne(["id" => $clientId]);

        if (!$client) {
            throw new \Exception("Клиент не найден");
        }

        $tariffId = self::getDefaultTariffId($region, $client->currency);

        if (!$tariffId) {
            throw new \Exception("Тариф не найден");
        }


        $transaction = Yii::$app->db->beginTransaction();
        try {
            //Создаем запись услуги
            $form = new UsageVoipEditForm;
            $form->scenario = 'add';
            $form->initModel($client);

            $form->tariff_main_id = $tariffId;
            $form->connecting_date = ($isForceStart ? date("Y-m-d") : \app\models\usages\UsageInterface::MAX_POSSIBLE_DATE);
            $form->did = $number;
            $form->no_of_lines = $lineCount;

            $form->prepareAdd();

            if (!$form->validate() || !$form->add()) 
            {
                if ($form->errors)
                {
                    \Yii::error($form);
                    $errorKeys = array_keys($form->errors);
                    throw new \Exception($form->errors[$errorKeys[0]][0], 500);
                } else {
                    throw new \Exception("Unknown error", 500);
                }
            }
            $usageVoipId = $form->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            "tariff" => $tariffId,
            "usage_id" => $usageVoipId
        ];
    }

    /**
     * Возвращает тариф по-умолчанию в регионе подключения
     *
     * @param $regionId
     * @param $currency
     * @return bool|mixed
     * @throws Exception
     */
    public static function getDefaultTariffId($regionId, $currency)
    {
        $tariff = TariffVoip::findOne([
            'status' => TariffVoip::STATUS_TEST,
            'connection_point_id' => $regionId,
            'currency_id' => $currency
        ]);

        if (!$tariff) {
            if (YII_ENV != "test" && defined("ADMIN_EMAIL") && ADMIN_EMAIL) {
                mail(ADMIN_EMAIL, "VoipReserveNumber",
                    "Тариф не установлен. region: " . $regionId . ", currency: " . $currency);
            }

            throw new \Exception("Тариф не установлен");
        }


        return $tariff->id;
    }
}
