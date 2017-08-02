<?php

namespace app\controllers\voip;

use app\classes\ReturnFormatted;
use app\dao\billing\TrunkDao;
use app\dao\ClientContractDao;
use app\models\billing\TrunkGroup;
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
                        'actions' => [
                            'index',
                            'get-logical-trunks',
                            'get-physical-trunks',
                            'get-contracts',
                            'get-regions',
                            'get-cities',
                            'get-trunk-groups'
                        ],
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
     * @param array $contractIds
     * @param array $trunkIds
     */
    public function actionGetLogicalTrunks(array $serverIds = [], array $contractIds = [], array $trunkIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            ServiceTrunk::getListWithName(
                [
                    'serverIds' => array_filter($serverIds),
                    'contractIds' => array_filter($contractIds),
                    'trunkIds' => array_filter($trunkIds),
                ]
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
     * @param array $trunkIds
     */
    public function actionGetContracts(array $serverIds = [], array $serviceTrunkIds = [], array $trunkIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            ClientContractDao::getListWithType(
                [
                    'serverIds' => array_filter($serverIds),
                    'serviceTrunkIds' => array_filter($serviceTrunkIds),
                    'trunkIds' => array_filter($trunkIds),
                ]
            ),
            'options'
        );
    }

    /**
     * Получить список физических транков с именами в качестве ключей
     *
     * @param array $serverIds
     * @param array $serviceTrunkIds
     * @param array $contractIds
     */
    public function actionGetPhysicalTrunks(array $serverIds = [], array $trunkGroupIds = [], array $serviceTrunkIds = [], array $contractIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            TrunkDao::me()->getList(
                [
                    'serverIds' => array_filter($serverIds),
                    'trunkGroupIds' => array_filter($trunkGroupIds),
                    'serviceTrunkIds' => array_filter($serviceTrunkIds),
                    'contractIds' => array_filter($contractIds),
                    'showInStat' => false,
                ]
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
                $isWithNullAndNotNull = true,
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
                $isWithNullAndNotNull = true,
                array_filter($countryCodes),
                array_filter($regionIds)
            ),
            'options'
        );
    }

    /**
     * Получить группы транков с фильтрацией по серверу
     *
     * @param array $serverIds
     */
    public function actionGetTrunkGroups(array $serverIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            TrunkGroup::getList($serverIds),
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

        if (!isset(Yii::$app->request->get()['_pjax'])) {
            return $this->render(
                'index',
                [
                    'filterModel' => $model
                ]
            );
        }

        return $this->renderPartial(
            'index',
            [
                'filterModel' => $model
            ]);
    }

}
