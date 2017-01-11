<?php

namespace app\controllers\voip;

use app\classes\ReturnFormatted;
use app\dao\ClientContractDao;
use Yii;
use app\models\voip\filter\Cdr;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\billing\ServiceTrunk;

/**
 * Контроллер страницы /voip/cdr (отчет по calls_cdr)
 *
 * Class CdrController
 */
class CdrController extends BaseController
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
                Yii::$app->request->get()['trunkName']
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
                Yii::$app->request->get()['trunkName']
            ),
            'options'
        );
    }

    /**
     * Контроллер страницы /voip/cdr
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new Cdr();
        $model->load(Yii::$app->request->get());

        return $this->render(
            'index',
            [
                'filterModel' => $model
            ]
        );
    }

}
