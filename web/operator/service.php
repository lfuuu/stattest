<?php

use app\classes\api\ApiCore;
use app\classes\api\Errors;
use app\dao\reports\ReportUsageDao;
use app\forms\client\ClientCreateExternalForm;
use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\City;
use app\models\ClientAccount;
use app\models\filter\FreeNumberFilter;
use app\models\TariffVoip;
use app\modules\nnp\models\NdcType;
use yii\web\BadRequestHttpException;

if (isset($_GET) && isset($_GET["test"])) {
    define('YII_ENV', 'test');
}

define('NO_WEB', 1);
define("PATH_TO_ROOT", '../../stat/');
header("Content-Type: text/html; charset=UTF-8");
include PATH_TO_ROOT . "conf_yii.php";

$db->Connect();

class UserService
{
    function Get($field)
    {
        return 25;
    }
}


$user = new UserService();

$action = get_param_raw('action', '');

if ($action == 'add_client') {


    $V = [
        'company' => 'company',
        'fio' => 'fio',
        'contact' => 'contact_phone',
        'email' => 'email',
        'phone' => 'official_phone',
        'fax' => 'fax',
        'address' => 'address',
        'client_comment' => 'comment',
        'site_name' => 'site_name',
        'vats_tariff_id' => 'vats_tariff_id',
        'connect_region' => 'connect_region',
        'ip' => 'ip',
        'entry_point_id' => 'entry_point_id'
    ];

    $P = [];
    foreach ($V as $k1 => $k2) {
        $P[$k2] = trim(get_param_raw($k1));
    }

    $P['numbers'] = get_param_raw('numbers', null);

    $isOldSchema = $P['numbers'] === null;

    if ($P['ip']) {
        $ipBlocker = \app\classes\IpBlocker::me();
        if ($ipBlocker->isBlocked($P['ip'])) {
            echo "error:" . Errors::ERROR_EXECUTE;
            exit();
        } else {
            $ipBlocker->block($P['ip']);
        }
    }

    if ($P["company"] == "google") {
        echo "error:" . Errors::ERROR_EXECUTE;
        exit();
    }

    if (!$P['company']) {
        $P['company'] = 'Клиент с сайта';
    }

    if (!$P['site_name']) {
        $P['site_name'] = 'mcn.ru';
    }


    $f = new ClientCreateExternalForm;
    $f->setAttributes($P);

    $result_message = "error:" . Errors::ERROR_INTERNAL;

    $transaction = null;
    try {
        if (!$f->validate()) {
            $errors = $f->getErrors();
            $fields = array_keys($errors);

            throw new \Exception($errors[$fields[0]][0]);
        }

        //check email
        if (!(defined("YII_ENV") && YII_ENV == "test")) {
            if (ApiCore::isEmailExists($P['email'])) {
                throw new BadRequestHttpException('E-mail уже зарегистрирован.', Errors::ERROR_EMAIL_ALREADY);
            }
        } elseif (!$isOldSchema && \app\models\ClientContact::findOne(['type' => 'email', 'data' => $P['email']])) {
            throw new BadRequestHttpException('E-mail уже зарегистрирован.', Errors::ERROR_EMAIL_ALREADY);
        }

        $transaction = Yii::$app->db->beginTransaction();

        if (!$f->create()) {
            throw new \Exception('Ошибка создания клиента');
        }

        $info = $f->account_id; //ask: "ok:123456"

        if ($isOldSchema) {  //старая схема не передает номера
            $info = $f->account_id . ($f->info ? ":" . $f->info : "");
        } elseif ($P['numbers']) {
            if (!VoipReserveNumber::reserveNumbers($f->account_id, explode(',', $P['numbers']))) {
                throw new BadRequestHttpException('Произошла ошибка резерва.', Errors::ERROR_RESERVE);
            }
        }
        $transaction->commit();

        //ok

        // Ожидание синхронизации с платформой
        if (defined("YII_ENV") && YII_ENV == "test") {
            die('ok:' . $info);
        }

        $startTime = time();
        $iteration = 1;
        do {
            sleep($iteration++);
            if (ApiCore::isEmailExists($P['email'])) {
                die('ok:' . $info);
            }
        } while ($startTime + 60 > time());

        throw new BadRequestHttpException('Ваша заявка поставлена в очередь, с вами свяжется менеджер.', Errors::ERROR_TIMEOUT);


    } catch (BadRequestHttpException $e) {
        \Yii::warning($e);
        $result_message = 'error:' . $e->getCode();
    } catch (\Exception $e) {
        \Yii::error($e);
        $result_message = 'error:' . Errors::ERROR_INTERNAL; //Произошла внутренняя ошибка.
    }

    if (!is_null($transaction)) {
        $transaction->rollBack();
    }

    die($result_message);

} elseif ($action == "set_active") {

    if (get_param_raw("password", "") != "7f6a7f509ddd33f21b4f165aebdab4be") {
        echo "error:wrong password";
        exit();
    }

    $bill_no = get_param_raw("bill_no", "");

    if ($b = $db->GetValue("select bill_no from newbills where bill_no = '" . $db->escape($bill_no) . "'")) {
        $db->Query("update newbills set editor = 'stat' where bill_no = '" . $b . "'");
        $t = $db->GetRow("select id, cur_stage_id from tt_troubles where bill_no = '" . $b . "'");
        if ($t) {
            $s = $db->GetRow("select * from tt_stages where stage_id = '" . $t["cur_stage_id"] . "'");

            $dateStart = date("Y-m-d H:i:s");
            $dateStart2 = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $R = [
                "trouble_id" => $t["id"],
                "state_id" => $s["state_id"],
                "user_main" => $s["user_main"],
                "date_start" => $dateStart,
                "date_finish_desired" => $dateStart2
            ];

            $s["date_edit"] = $s["date_finish_desired"] = ["NOW()"];


            $comment = get_param_raw("comment", "");
            $s["comment"] = ($comment ? $comment . "<hr>" : "") . "заявка передана в stat";

            $db->QueryUpdate("tt_stages", "stage_id", $s);
            $sId = $db->QueryInsert("tt_stages", $R);
            $db->Query("update tt_troubles set cur_stage_id = '" . $sId . "' where id = '" . $t["id"] . "'");
        }
        echo "ok";
    } else {
        echo "error: bill not found";
    }

} elseif ($action == "get_free_numbers") {
    $region = isset($_GET["region"]) ? (int)$_GET["region"] : null;

    $numbersFilter = new FreeNumberFilter;
    $numbersFilter
        ->setIsService(false)
        ->setNdcType(NdcType::ID_GEOGRAPHIC)
        ->setIsShowInLk(City::IS_SHOW_IN_LK_FULL)
        ->setRegions([$region]);

    foreach ($numbersFilter->result() as $number) {
        echo implode(';', [$number->number, $number->beauty_level, $number->price, $number->region]) . "\n";
    }

} elseif ($action == "reserve_number") {
    $client_id = get_param_integer("client_id", 0);
    $numbers = get_param_protected("number", "");
    $_numbers = explode(',', $numbers);

    try {
        $isOk = VoipReserveNumber::reserveNumbers($client_id, $_numbers);
    } catch (\Exception $e) {
        $isOk = false;
        \Yii::error($e);
    }

    die($isOk ? "1" : "0");

} elseif ($action == "connect_line") {
    $clientId = get_param_raw("client_id", 0);
    $tarifId = get_param_raw("tarif_id", 0);

    try {
        $client = ClientAccount::findOne(["id" => $clientId]);

        if (!$client) {
            throw new Exception("Клиент не найден");
        }

        $tarif = TariffVoip::findOne(
            [
                "connection_point_id" => $client->region,
                "currency_id" => $client->currency,

                "id" => $tarifId
            ]
        );

        if (!$tarif) {
            throw new Exception("Тариф не найден");
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $form = new UsageVoipEditForm;
            $form->scenario = 'add';
            $form->initModel($client);

            $form->city_id = $client->region;
            $form->tariff_main_id = $tarif->id;
            $form->type_id = "line";

            $form->prepareAdd();

            if (!$form->validate() || !$form->add()) {
                if ($form->errors) {
                    \Yii::error($form);
                    $errorKeys = array_keys($form->errors);
                    throw new \Exception($form->errors[$errorKeys[0]][0], 500);
                } else {
                    throw new \Exception("Unknown error", 500);
                }
            }
            $transaction->commit();

            echo "ok:" . $form->did;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    } catch (\Exception $e) {
        echo "error:" . $e->GetMessage();
    }

} elseif ($action == "stat_voip") {
    if (!isset($_GET["d"])) {
        die("error: empty params");
    }

    $s = new m_stats();

    $d = $_GET["d"];
    if (!($d = @unserialize($d))) {
        die("error: params is bad");
    }

    list($region, $from, $to, $detality, $client_id, $usage_arr, $paidonly, $skipped, $destination, $direction, $timezone) = $d;

    $dt = new DateTime();
    $dt->setTimeZone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));

    $a = ReportUsageDao::me()->getUsageVoipStatistic(
        $region,
        $from + $dt->getOffset(),
        $to + $dt->getOffset(),
        $detality,
        $client_id,
        $usage_arr,
        $paidonly,
        $destination,
        $direction
    );


    echo serialize($a);


} elseif ($action == "stat_find") {
    if (!isset($_GET["d"])) {
        die("error: empty params");
    }

    $s = new m_stats();

    $d = $_GET["d"];
    if (!($d = @unserialize($d))) {
        die("error: params is bad");
    }

    list($region, $from, $to, $find) = $d;

    $a = $s->FindByNumber($region, $from, $to, $find);

    echo serialize($a);
}