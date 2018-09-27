<?php
namespace app\controllers\report;

use app\classes\traits\AddClientAccountFilterTraits;
use Yii;
use DateTime;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use app\classes\DynamicModel;
use app\classes\BaseController;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\dao\reports\ReportUsageDao;

class VoipPackageController extends BaseController
{
    use AddClientAccountFilterTraits;

    const FILTER_VOIP_PACKAGE_BY_PACKAGE = 'by_package';
    const FILTER_VOIP_PACKAGE_BY_PACKAGE_CALLS = 'by_package_calls';

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['stats.r'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUseReport()
    {
        if (!$this->_getCurrentClientAccountId()) {
            Yii::$app->session->addFlash('error', 'Выберите клиента');
        }

        $data = Yii::$app->request->get('filter');
        $report = [];
        $filter = null;

        if ($data) {
            /** @var DynamicModel $filter */
            $filter = DynamicModel::validateData($data,
                [
                    ['number', 'integer'],
                    ['number', 'required', 'message' => 'Необходимо выбрать номер'],
                    [
                        'mode',
                        'in',
                        'range' => [self::FILTER_VOIP_PACKAGE_BY_PACKAGE, self::FILTER_VOIP_PACKAGE_BY_PACKAGE_CALLS]
                    ],
                    [['range', 'date_range_from', 'date_range_to'], 'string'],
                    ['range', 'required', 'message' => 'Необходимо указать период'],
                    [
                        'packages',
                        'required',
                        'when' => function ($model) {
                            return $model->mode === 'by_package_calls' && $model->packages != 0;
                        },
                        'message' => 'Необходимо выбрать пакет'
                    ],
                ]
            );

            if ($filter->hasErrors()) {
                Yii::$app->session->setFlash('error', implode('<br />' . PHP_EOL, $filter->getFirstErrors()));
            } else {
                /** @var UsageVoip $usage */
                $usage = UsageVoip::findOne($filter->number);
                list($filter->date_range_from, $filter->date_range_to) = explode(':', $filter->range);

                switch ($filter->mode) {
                    case self::FILTER_VOIP_PACKAGE_BY_PACKAGE: {
                        $report = new ActiveDataProvider([
                            'query' => ReportUsageDao::me()->getUsageVoipPackagesStatistic($usage->id, $filter->packages),
                            'sort' => false,
                        ]);
                        break;
                    }
                    case self::FILTER_VOIP_PACKAGE_BY_PACKAGE_CALLS: {
                        $report = new ArrayDataProvider([
                            'allModels' =>
                                ReportUsageDao::me()->getUsageVoipStatistic(
                                    $usage->region,
                                    (new DateTime($filter->date_range_from))->getTimestamp(),
                                    (new DateTime($filter->date_range_to))->getTimestamp(),
                                    $detality = 'call',
                                    $usage->clientAccount->id,
                                    [$usage->id],
                                    $paidonly = 0,
                                    $destination = 'all',
                                    $direction = 'both',
                                    $is_full = false,
                                    ($filter->packages ? [$filter->packages] : [])
                                ),
                            'sort' => false,
                            'pagination' => false,
                        ]);
                        break;
                    }
                }
            }
        }

        $numbers = UsageVoip::find()->client($fixclient_data->client)->actual()->indexBy('id')->all();
        $packages = UsageVoipPackage::find()->client($fixclient_data->client)->all();

        return $this->render(
            'use-report',
            [
                'clientAccount' => $fixclient_data,
                'numbers' => $numbers,
                'packages' => $packages,
                'report' => $report,
                'filter' => $filter,
            ]
        );
    }

}