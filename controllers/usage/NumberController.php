<?php
namespace app\controllers\usage;

use Yii;
use DateTime;
use yii\helpers\ArrayHelper;
use app\classes\Assert;
use app\forms\usage\NumberForm;
use app\models\City;
use app\models\DidGroup;
use app\models\Number;
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
        $cityId = Yii::$app->request->post('cityId', Yii::$app->user->identity->city_id);
        $didGroups = Yii::$app->request->post('didGroups');
        $statuses = Yii::$app->request->post('statuses');
        $prefix = Yii::$app->request->post('prefix');
        $viewType = Yii::$app->request->post('view-minimal');

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
                ")->cache(86400)->queryAll();

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

        if (!empty($viewType)) {
            $result = implode("\r\n", ArrayHelper::getColumn($numbers, 'number'));
            Yii::$app->response->sendContentAsFile($result, 'numbers--' . (new DateTime('now'))->format('Y-m-d') . '.txt');
            Yii::$app->end();
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

}