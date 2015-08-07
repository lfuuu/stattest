<?php
namespace app\controllers\usage;

use app\classes\Assert;
use app\forms\usage\NumberForm;
use app\models\City;
use app\models\DidGroup;
use app\models\Number;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\classes\BaseController;

class NumberController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['services_voip.e164'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['detail-report'],
                        'roles' => ['stats.report'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($did)
    {
        $actionForm = new NumberForm();
        if ($actionForm->load(Yii::$app->request->post()) && $actionForm->validate() && $actionForm->process()) {
            return $this->redirect(['view', 'did' => $did]);
        }
        if ($actionForm->hasErrors()) {
            foreach ($actionForm->firstErrors as $error) {
                Yii::$app->session->addFlash('error', $error);
            }
        }
        $actionForm->scenario = 'default';
        $actionForm->did = $did;
        global $fixclient_data;
        $actionForm->client_account_id = $fixclient_data ? $fixclient_data['id'] : null;

        $number = Number::findOne($did);
        Assert::isObject($number);

        $logList =
            Yii::$app->db->createCommand("
                select
                    date_format(`es`.`time`,'%Y-%m-%d %H:%i:%s') `human_time`,
                    `uu`.`user`,
                    `es`.`user` `user_id`,
                    `cl`.`client`,
                    `es`.`client` `client_id`,
                    `es`.`addition`,
                    `es`.`action`
                from `e164_stat` `es`
                left join `clients` `cl` on `cl`.`id` = `es`.`client`
                left join `user_users` `uu` on `uu`.`id` = `es`.`user`
                where `es`.`e164`= :did
                order by `es`.`time` desc
            ", [
                ':did' => $number->number
            ])->queryAll();

        return $this->render('view', [
            'number' => $number,
            'logList' => $logList,
            'actionForm' => $actionForm,
        ]);
    }

    public function actionDetailReport()
    {
        $cityId = Yii::$app->request->post('cityId', 0);
        $didGroups = Yii::$app->request->post('didGroups');
        $statuses = Yii::$app->request->post('statuses');
        $prefix = Yii::$app->request->post('prefix');

        $cityList = City::dao()->getList(true);
        $didGroupList = DidGroup::dao()->getList(false, $cityId);

        if (!$didGroups) {
            $didGroups = array_keys($didGroupList);
        }
        if (!$statuses) {
            $statusList= Number::$statusList;
            unset($statusList[Number::STATUS_HOLD]);
            $statuses = array_keys($statusList);
        }

        $numbers =
            Yii::$app->db->createCommand("
                    select n.*, ccc.name as company, c.client from voip_numbers n
                    left join clients c on n.client_id=c.id
                    left join client_contract cc on cc.id=c.contract_id
                    left join client_contragent ccc on ccc.id=cc.contragent_id
                    where
                        n.city_id = :cityId
                        and n.did_group_id in ('" . implode("','", $didGroups) . "')
                        and n.status in ('" . implode("','", $statuses) . "')
                        and n.number like :prefix
                ", [
                ':cityId' => $cityId,
                ':prefix' => $prefix . '%',
            ])->queryAll();

        $city = City::findOne($cityId);
        if ($city && $city->connection_point_id && in_array('hold', $statuses)) {
            $callsCount =
                Yii::$app->dbPg->createCommand("
                    select dst_number as usage_num, count(*) / 3 as count_avg3m
                    from calls_raw.calls_raw
                    where connect_time > now() - interval '3 month'
                    and server_id = $city->connection_point_id
                    and number_service_id is null
                    and orig = false
                    group by dst_number
                ")->queryAll();
            $callsCountByNumber = [];
            foreach ($callsCount as $calls) {
                $callsCountByNumber[$calls['usage_num']] = $calls['count_avg3m'];
            }

            foreach($numbers as $k => $n) {
                if (isset($callsCountByNumber[$n["number"]])) {
                    $numbers[$k]['count_avg3m'] = $callsCountByNumber[$n["number"]];
                }
            }
        }

        return $this->render('detail-report', [
            'cityId' => $cityId,
            'didGroups' => $didGroups,
            'statuses' => $statuses,
            'prefix' => $prefix,
            'cityList' => $cityList,
            'didGroupList' => $didGroupList,
            'statusList' => Number::$statusList,
            'numbers' => $numbers,
            'minCalls' => 10 //минимальное среднее кол-во звоноков за 3 месяца в месяц, для возможности публиковать номер минуя "отстойник"
        ]);
    }


    function stats_voip_free_stat($fixclient)
    {
        global $db, $pg_db, $design;


        $ns = array();
        $groups = array("used" => "Используется", "free" => "Свободный", "our" => "ЭмСиЭн", "reserv" => "Резерв", "stop" => "Отстойник");
        $beautys = array("0" => "Стандартные", "4" => "Бронза", "3" => "Серебро", "2" => "Золото", "1" => "Платина (договорная цена)");

        $numberRanges = array(
            "74996850000" => array("74996850000", "74996850199", "Москва"),
            "74996851000" => array("74996851000", "74996851999", ""),
            "74992130000" => array("74992130000", "74992130499", ""),
            "74992133000" => array("74992133000", "74992133999", ""),

            "74956380000" => array("74956380000", "74956389999", ""),
            "74959500000" => array("74959500000", "74959509999", ""),
            "74951059000" => array("74951059000", "74951059999", ""),

            "78612040000" => array("78612040000", "78612040499", "Краснодар"), //КРАСНОДАР
            "78123726500" => array("78123726500", "78123726999", "Санкт-Петербург"), //САНКТ-ПЕТЕРБУРГ
            "78462150000" => array("78462150000", "78462150499", "Самара"), //САМАРА
            "73433020000" => array("73433020000", "73433022999", "Екатеринбург"), //ЕКАТЕРИНБУРГ
            "73833120000" => array("73833120000", "73833120499", "Новосибирск"), //НОВОСИБИРСК
            "78633090000" => array("78633090000", "78633090499", "Ростов-на-дону"), //РОСТОВ-НА-ДОНУ
            "78432070000" => array("78432070000", "78432070499", "Казань"), //КАЗАНЬ
            "74232060000" => array("74232060000", "74232060499", "Владивосток"), //ВЛАДИВОСТОК
        );

        $rangeFrom = get_param_raw("range_from", '74996850000');
        $rangeTo = $numberRanges[$rangeFrom][1];

        $cityId = get_param_raw("cityId", 0);
        $didGroups = get_param_raw("didGroups");
        $statuses = get_param_raw("statuses");
        $group = get_param_raw("group",array_keys($groups));
        $beauty = get_param_raw("beauty",array_keys($beautys));
        $cityList = \app\models\City::dao()->getList(true);
        $didGroupList = \app\models\DidGroup::dao()->getList(false, $cityId);
        $statusList = \app\models\Number::$statusList;


        if (!$didGroups) {
            $didGroups = array_keys($didGroupList);
        }
        if (!$statuses) {
            $statuses = array_keys($statusList);
        }

        $design->assign('cityList', $cityList);
        $design->assign('didGroupList', $didGroupList);
        $design->assign('statusList', $statusList);
        $design->assign('cityId', $cityId);
        $design->assign('didGroups', $didGroups);
        $design->assign('statuses', $statuses);
        $design->assign("ranges", $numberRanges);
        $design->assign("range_from", $rangeFrom);
        $design->assign("group", $group);
        $design->assign("groups", $groups);
        $design->assign("beauty", $beauty);
        $design->assign("beautys", $beautys);

        $design->assign("minCalls", 10); //минимальное среднее кол-во звоноков за 3 месяца в месяц, для возможности публиковать номер минуя "отстойник"

        if (get_param_raw("do","") && get_param_raw("make"))
        {
            /*
            $ns = $db->AllRecords($q = "
                        SELECT
                            a.*, c.company, c.client,
                            IF(client_id IN ('9130', '764'), 'our',
                                IF(date_reserved IS NOT NULL, 'reserv',
                                    IF(active_usage_id IS NOT NULL, 'used',
                                        IF(max_date >= (now() - INTERVAL 6 MONTH), 'stop', 'free'
                                        )
                                    )
                                )
                            ) AS calc_status
                        FROM (
                            SELECT
                                number,
                                region,
                                price,
                                client_id,
                                usage_id,
                                cast(used_until_date as date) used_until_date,
                                beauty_level,
                                status,
                                (
                                    SELECT
                                        MAX(actual_to)
                                    FROM
                                        usage_voip u
                                    WHERE
                                        u.e164 = v.number AND
                                        actual_from <= DATE_FORMAT(now(), '%Y-%m-%d')
                                ) AS max_date,
                                (
                                    SELECT
                                        MAX(id)
                                    FROM
                                        usage_voip u
                                    WHERE
                                        u.e164 = v.number AND
                                        (
                                            (
                                                actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') AND
                                                actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                                            ) OR
                                            actual_from >= '2029-01-01'
                                        )
                                ) as active_usage_id,

                                (
                                    SELECT
                                        MAX(ts)
                                    FROM
                                        log_tarif lt, usage_voip u
                                    WHERE
                                        u.e164 = v.number AND
                                        lt.service = 'usage_voip' AND
                                        u.id = lt.id_service AND
                                        u.actual_from = '2029-01-01' AND
                                        u.actual_to = '2029-01-01' AND
                                        u.status = 'connecting'
                                    GROUP BY lt.id_service
                                ) AS date_reserved
                            FROM
                                voip_numbers v
                            WHERE
                                number BETWEEN '".$rangeFrom."' AND '".$rangeTo."'
                        )a
                        LEFT JOIN clients c ON (c.id = a.client_id)
                        WHERE beauty_level IN ('".implode("','", $beauty)."')
                        HAVING calc_status IN ('".implode("','", $group)."')
                    ");
*/
            $fromTime = strtotime("first day of -3 month, midnight");

            $numbers =
                Yii::$app->db->createCommand("
                    select n.*, c.company, c.client from voip_numbers n
                    left join clients c on n.client_id=c.id
                    where n.city_id = :cityId and n.did_group_1id in ('" . implode("','", $didGroups) . "' and n.status in ('" . implode("','", $statuses) . "')
                    limit 10
                ", [
                    ':cityId' => $cityId
                ])->queryAll();
            foreach($numbers as &$n)
            {
                $n["calls"] = "";
                $n["count_3m"] = 0;

                if ($n["status"] == "hold") {
                    foreach($pg_db->AllRecords("
                        select to_char(time, 'Mon') as mnth_s, to_char(time, 'MM') as mnth,
                            sum(1) as count_calls,
                            sum(case when time between now() - interval '3 month' and now() then 1 else 0 end) count_3m
                        from calls.calls_".$n['region']."
                        where time > '".date("Y-m-d H:i:s", $fromTime)."'
                        and usage_id is null
                        and region=".$n['region']."
                        and usage_num = '".$n["number"]."'
                        group by mnth, mnth_s
                        order by mnth
                    ") as $c)
                    {
                        $n["calls"] .= ($n["calls"] ? ", " : "").$c["mnth_s"].": ".$c["count_calls"];
                        $n["count_3m"] += $c["count_3m"];
                    }
                }

                if($n["count_3m"])
                {
                    $n["count_avg3m"] = round($n["count_3m"]/3, 2);
                }
            }
        }

        $design->assign("ns", $numbers);
        $design->assign("ns_count", count($numbers));


    }

}