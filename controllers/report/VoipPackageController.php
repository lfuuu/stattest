<?php
namespace app\controllers\report;

use Yii;
use DateTime;
use yii\filters\AccessControl;
use app\classes\DynamicModel;
use app\classes\BaseController;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\dao\reports\ReportUsageDao;

class VoipPackageController extends BaseController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['stats.r'],
                    ],
                ],
            ],
        ]);
    }

    public function actionUseReport()
    {
        global $fixclient_data;

        if (!($fixclient_data instanceof ClientAccount)) {
            Yii::$app->session->addFlash('error', 'Выберите клиента');
        }

        $data = Yii::$app->request->get('filter');
        $report = [];
        $filter = null;

        if ($data) {
            $filter = DynamicModel::validateData($data, [
                ['number', 'integer'],
                ['number', 'required', 'message' => 'Необходимо выбрать номер'],
                ['mode', 'in', 'range' => ['by_package', 'by_package_calls']],
                [['range', 'date_range_from', 'date_range_to'], 'string'],
                ['range', 'required', 'message' => 'Необходимо указать период'],
                ['packages', 'required', 'when' => function ($model) {
                    return $model->mode === 'by_package_calls' && $model->packages != 0;
                }, 'message' => 'Необходимо выбрать пакет'],
            ]);

            if ($filter->hasErrors()) {
                Yii::$app->session->setFlash('error', $filter->getFirstErrors());
            }
            else {
                $usage = UsageVoip::findOne($filter->number);
                list($filter->date_range_from, $filter->date_range_to) = explode(':', $filter->range);

                switch ($filter->mode) {
                    case 'by_package': {
                        $report = ReportUsageDao::getUsageVoipPackagesStatistic(
                            $usage->id,
                            $filter->packages,
                            (new DateTime($filter->date_range_from))->setTime(0, 0, 0),
                            (new DateTime($filter->date_range_to))->setTime(23, 59, 59)
                        );
                        break;
                    }
                    case 'by_package_calls': {
                        $report = ReportUsageDao::getUsageVoipStatistic(
                            $usage->region,
                            (new DateTime($filter->date_range_from))->getTimeStamp(),
                            (new DateTime($filter->date_range_to))->getTimeStamp(),
                            $detality = 'call',
                            $usage->clientAccount->id,
                            [$usage->id],
                            $paidonly = 0, $destination = 'all',
                            $direction = 'both',
                            $timezone = 'Europe/Moscow',
                            $is_full = false,
                            ($filter->packages ? [$filter->packages] : [])
                        );
                        break;
                    }
                }
            }
        }

        $numbers = UsageVoip::find()->client($fixclient_data->client)->all();
        $packages = UsageVoipPackage::find()->client($fixclient_data->client)->all();

        return $this->render('use-report', [
            'clientAccount' => $fixclient_data,
            'numbers' => $numbers,
            'packages' => $packages,
            'report' => $report,
            'filter' => $filter,
        ]);
    }

}