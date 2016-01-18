<?php
namespace app\controllers\report;

use app\classes\report\LostCalls;
use app\dao\VoipDestinationDao;
use app\models\billing\GeoCountry;
use app\models\billing\GeoRegion;
use app\models\billing\Server;
use app\models\billing\Trunk;
use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

class VoipController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['lost-calls', 'cost-report'],
                'roles' => ['clients.read'],
            ],
        ];

        return $behaviors;
    }

    public function actionCostReport()
    {
        if (\Yii::$app->request->getIsAjax()) {

            $post = \Yii::$app->request->post();

            switch ( $post['operation'] ) {

                case 'update_trunks':
                    $query = Trunk::find()->where('show_in_stat = true');

                    if (!empty($post['server_id'])) {
                        $query->andWhere('server_id = :serverId', [':serverId' => $post['server_id']]);
                    }

                    $result = $query->all();
                    $json = [];

                    if ($result) {
                        $json['status'] = 'success';

                        foreach ($result as $trunk) {
                            $json['data'][] = [
                                'id' => $trunk['id'],
                                'text' => $trunk['name'],
                            ];
                        }
                    }

                    return json_encode( $json, JSON_UNESCAPED_UNICODE );

                    break;
            }
        }

        ini_set('max_execution_time', 9000);

        $query = (new Query())
            ->select([
                'rc.prefix AS prefix',
                'rc.trunk_id',
                'rc.mob as mob',

                'g.name AS destination',

                'COUNT(rc.id) AS calls_count',
                'SUM(rc.cost) AS cost',
                'SUM(rc.billed_time) AS billed_time',
                'SUM(rc.interconnect_cost) AS interconnect_cost',
            ])
            ->from('calls_raw.calls_raw rc')

            ->leftJoin('public.voip_destinations vd', 'vd.ndef = rc.destination_id' )
            ->leftJoin('geo.geo g', 'vd.geo_id = g.id')


            ->andWhere('rc.destination_id IS NOT NULL')

            ->groupBy([
                'rc.prefix',
                'rc.trunk_id',
                'rc.mob',
                'rc.cost',
                'rc.interconnect_cost',
                'destination',
            ]);

        $this->addFilters($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'db' => \Yii::$app->dbPg,

            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('cost',
            [
                'dataProvider' => $dataProvider,

                'trunkModel' => Trunk::find()->where(['show_in_stat' => true])->all(),
                'regionModel' => GeoRegion::find()->all(),
                'destination' => VoipDestinationDao::me()->getList(),
                'trunk' => Trunk::find()->all(),
                'server' => Server::find()->all(),
                'geoCountry' => GeoCountry::find()->all(),
                'totals' => $this->getTotalStat(),
            ]);
    }

    public function actionLostCalls($mode = LostCalls::CALL_MODE_OUTCOMING, $date = false, $region = 99)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $count = LostCalls::getCount($date, $region, $mode);
        if (!$count) {
            LostCalls::prepare($date, $region, $mode);
            $count = LostCalls::getCount($date, $region, $mode);
        }

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT * FROM tmp_calls_raw WHERE DATE(connect_time) = :date AND region = :region AND mode = :mode',
            'params' => [
                ':date' => $date,
                ':region' => $region,
                ':mode' => $mode,
            ],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('grid',
            ['dataProvider' => $dataProvider, 'date' => $date, 'region' => $region, 'mode' => $mode]);
    }

    private function addFilters(Query $query)
    {
        if ( !empty( \Yii::$app->request->get('trunk') ) ) {
            $query->andWhere('rc.trunk_id = :trunkId', [ ':trunkId' => 36 /*\Yii::$app->request->get('trunk')*/ ]);
        }

        if ( !empty( \Yii::$app->request->get('server') ) ) {
            $query->andWhere('rc.server_id = :serverId', [':serverId' => \Yii::$app->request->get('server')]);
        } else {
            $query->andWhere('rc.server_id = 99');
        }

        if ( !empty( \Yii::$app->request->get('operator') ) ) {
            $query->andWhere('rc.operator_id = :operatorId', [ ':operatorId' => \Yii::$app->request->get('operator') ]);
        } else {
            $query->andWhere('rc.operator_id = 2');
        }

        if ( !empty( \Yii::$app->request->get('dateRange') ) ) {
            list($startDate, $endDate) = explode(' - ', \Yii::$app->request->get('dateRange'));

            $query->andWhere('rc.connect_time BETWEEN :start AND :end', [':start' => $startDate, ':end' => $endDate]);
        } else {
            $query->andWhere('rc.connect_time BETWEEN :start AND :end', [':start' => date('Y-m-01'), ':end' => date('Y-m-d')]);
        }

        if ( !empty( \Yii::$app->request->get('mob_or_base') ) ) {
            switch (\Yii::$app->request->get('mob_or_base')) {
                case 2:
                    $query->andWhere('rc.mob = TRUE');
                    break;

                case 3:
                    $query->andWhere('rc.mob = FALSE');
                    break;
            }
        }

        if ( !empty( \Yii::$app->request->get('orig_term') ) ) {
            switch (\Yii::$app->request->get('orig_term')) {
                case 1:
                case 2:
                    break;

                case 3:
                    $query->andWhere('rc.orig = TRUE');
                    break;

                case 4:
                    $query->andWhere('rc.orig = FALSE');
                    break;
            }
        }

        if ( !empty( \Yii::$app->request->get('time') ) ) {
            switch (\Yii::$app->request->get('time')) {
                case 1:
                    $query->andWhere('rc.billed_time > 0');
                    break;

                case 2:
                    $query->andWhere('rc.billed_time = 0');
                    break;

                case 3: // don't needed, but for readability :)
                    break;
            }
        }


        if ( !empty( \Yii::$app->request->get('region') ) ) {
            $query->andWhere('g.region = :region', [ ':region' => \Yii::$app->request->get('region') ]);
        }

        if ( !empty( \Yii::$app->request->get('country') ) ) {
            $query->andWhere('g.country = :country', [ ':country' => \Yii::$app->request->get('country') ]);
        }

        $query->andWhere('NOT rc.our');
        $query->andWhere('NOT rc.orig');
    }

    private function getTotalStat()
    {
        $query = (new Query())
            ->select([
                'SUM(rc.cost) AS cost',
                'SUM(rc.billed_time) AS billed_time',
                'SUM(rc.interconnect_cost) AS interconnect_cost',
            ])
            ->from('calls_raw.calls_raw rc')

            ->leftJoin('public.voip_destinations vd', 'vd.ndef = rc.destination_id' )
            ->leftJoin('geo.geo g', 'vd.geo_id = g.id')

            ->andWhere('rc.destination_id IS NOT NULL');

        $this->addFilters($query);
        $baseStat = $query->one(\Yii::$app->dbPg);

        return $baseStat;
    }
}
