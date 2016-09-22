<?php
namespace app\controllers\usage;

use app\helpers\DateTimeZoneHelper;
use app\models\NumberType;
use Yii;
use DateTime;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\classes\Assert;
use app\classes\DateFunction;
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
        $numberType = Yii::$app->request->post('numberType');
        $statuses = Yii::$app->request->post('statuses');
        $prefix = Yii::$app->request->post('prefix');
        $viewTypeFile = Yii::$app->request->post('view-minimal');
        $viewType = $viewTypeFile || Yii::$app->request->post('make');

        $cityList = City::dao()->getList(true);
        $didGroupList = DidGroup::dao()->getList(false, $cityId);
        $numberTypeList = NumberType::getList();

        if (!$numberType) {
            $numberType = NumberType::ID_GEO_DID;
        }

        if ($numberType == NumberType::ID_GEO_DID && !$didGroups) {
            $didGroups = array_keys($didGroupList);
        }

        if (!$statuses) {
            $statusList = Number::$statusList;
            unset($statusList[Number::STATUS_NOTACTIVE_HOLD], $statusList[Number::STATUS_RELEASED]);
            $statuses = array_keys($statusList);
        }

        $headMonths = [];
        $numbers = [];

        if ($viewType) {
            $dt = new \DateTime();
            $dt->setDate(date('Y'), date('m'), 15);
            $dt->modify('-6 month');
            $emptyMonths = [];
            for ($i = 0; $i < 6; $i++) {
                $dt->modify('+1 month');
                $headMonths[(int)$dt->format('m')] = DateFunction::mdate($dt->getTimestamp(), 'месяц');
                $emptyMonths[(int)$dt->format('m')] = 0;
            }

            $numbersQuery = (new Query())
                ->select(['n.*', 'c.client'])
                ->addSelect(['company' => 'ccc.name'])
                ->from(['n' => 'voip_numbers'])
                ->leftJoin(['c' => 'clients'], 'n.client_id=c.id')
                ->leftJoin(['cc' => 'client_contract'], 'cc.id=c.contract_id')
                ->leftJoin(['ccc' => 'client_contragent'], 'ccc.id=cc.contragent_id')
                ->where([
                    'number_type' => $numberType,
                    'n.city_id' => $cityId,
                    'n.status' => $statuses
                ]);

            if ($numberType == NumberType::ID_GEO_DID) {
                $numbersQuery->andWhere(['did_group_id' => $didGroups]);
            }

            if ($prefix) {
                $numbersQuery->andWhere(['like', 'n.number', $prefix . '%', false]);
            }

            $numbers = $numbersQuery->createCommand()->queryAll();


            $city = City::findOne($cityId);
            if (
                $city
                &&
                $city->connection_point_id
                &&
                (
                    in_array(Number::STATUS_NOTACTIVE_HOLD, $statuses, true)
                    ||
                    in_array(Number::STATUS_INSTOCK, $statuses, true)
                )
            ) {

                $callsCount = Number::dao()->getCallsWithoutUsages($city->connection_point_id);

                $callsCountByNumber = [];
                foreach ($callsCount as $calls) {
                    if (!isset($callsCountByNumber[$calls['u']])) {
                        $callsCountByNumber[$calls['u']] = $emptyMonths;
                    }

                    $callsCountByNumber[$calls['u']][(int)$calls['m']] = $calls['c'];
                }

                foreach ($numbers as $k => $n) {
                    if (isset($callsCountByNumber[$n['number']])) {
                        $numbers[$k]['month'] = $callsCountByNumber[$n['number']];
                    }
                }
            }
        }

        if (!empty($viewTypeFile)) {
            $result = implode("\r\n", ArrayHelper::getColumn($numbers, 'number'));
            Yii::$app->response->sendContentAsFile($result,
                'numbers--' . (new DateTime('now'))->format(DateTimeZoneHelper::DATE_FORMAT) . '.txt');
            Yii::$app->end();
        }

        return $this->render('detail-report', [
            'cityId' => $cityId,
            'numberType' => $numberType,
            'didGroups' => $didGroups,
            'statuses' => $statuses,
            'prefix' => $prefix,
            'cityList' => $cityList,
            'numberTypeList' => $numberTypeList,
            'didGroupList' => $didGroupList,
            'statusList' => Number::$statusList,
            'numbers' => $numbers,
            'minCalls' => 10,
            //минимальное среднее кол-во звоноков за 3 месяца в месяц, для возможности публиковать номер минуя "отстойник"
            'headMonths' => $headMonths
        ]);
    }

}
