<?php
/**
 * Контроллер для отчета по звонкам 4-го и 5-го класса (/voip/combined-statistics)
 */

namespace app\controllers\voip;

use app\models\voip\filter\CombinedStatistics;
use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;

/**
 * Class CombinedStatisticsController
 * @package app\controllers\voip
 */
class CombinedStatisticsController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'links'],
                        'roles' => ['voip.access'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Controller for /voip/cdr-workload
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new CombinedStatistics();
        $model->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $model
        ]);
    }

}