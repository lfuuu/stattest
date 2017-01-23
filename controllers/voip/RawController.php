<?php

namespace app\controllers\voip;

use app\classes\ReturnFormatted;
use app\dao\ClientContractDao;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Region;
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
                        'actions' => ['index', 'get-routes', 'get-contracts', 'get-regions', 'get-cities'],
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
     * @param array $serverIds
     * @param array $serviceTrunkIds
     */
    public function actionGetRoutes(array $serverIds = [], array $serviceTrunkIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            ServiceTrunk::getListWithName(
                array_filter($serverIds),
                array_filter($serviceTrunkIds)
            ),
            'options'
        );
    }

    /**
     * Получить контракты транков с фильтрацией по
     * ID сервера и ID транка
     *
     * @param array $serverIds
     * @param array $serviceTrunkIds
     */
    public function actionGetContracts(array $serverIds = [], array $serviceTrunkIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            ClientContractDao::getListWithType(
                array_filter($serverIds),
                array_filter($serviceTrunkIds)
            ),
            'options'
        );
    }

    /**
     * Получить ННП-регионы с фильтрацией по стране
     *
     * @param array $countryCodes
     */
    public function actionGetRegions(array $countryCodes = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            Region::getList(
                $isWithEmpty = false,
                $isWithNullAndNotNull = false,
                array_filter($countryCodes)
            ),
            'options'
        );
    }

    /**
     * Получить ННП-города с фильтрацией по стране
     *
     * @param array $countryCodes
     * @param array $regionIds
     */
    public function actionGetCities(array $countryCodes = [], array $regionIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            City::getList(
                $isWithEmpty = false,
                $isWithNullAndNotNull = false,
                array_filter($countryCodes),
                array_filter($regionIds)
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
