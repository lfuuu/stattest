<?php

namespace app\controllers\voip;

use app\classes\ReturnFormatted;
use app\dao\ClientContractDao;
use Yii;
use app\models\voip\filter\CallsRawFilter;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\billing\ServiceTrunk;

/**
 * Контроллер страницы /voip/raw (отчет по calls_raw)
 *
 * Class RawController
 */
class RawController extends BaseController
{
    /**
     * Права доступа
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'get-routes', 'get-contracts'],
                        'roles' => ['voip.access'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Получить транки с фильтрацией по
     * ID сервера и ID контракта
     *
     * @return array
     */
    public function actionGetRoutes()
    {
        ReturnFormatted::me()->returnFormattedValues(
            ServiceTrunk::getListWithName(
                Yii::$app->request->get()['serverIds'],
                Yii::$app->request->get()['serviceTrunkId']
            ),
            'options'
        );
    }

    /**
     * Получить контракты транков с фильтрацией по
     * ID сервера и ID транка
     *
     * @return array
     */
    public function actionGetContracts()
    {
        ReturnFormatted::me()->returnFormattedValues(
            ClientContractDao::getListWithType(
                Yii::$app->request->get()['serverIds'],
                Yii::$app->request->get()['serviceTrunkId']
            ),
            'options'
        );
    }

    /**
     * Контроллер страницы /voip/raw
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new CallsRawFilter();
        $model->load(Yii::$app->request->get());

        return $this->render(
            'index',
            [
                'filterModel' => $model
            ]
        );
    }

}
