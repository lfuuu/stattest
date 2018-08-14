<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\ReturnFormatted;
use app\dao\billing\TrunkDao;
use app\exceptions\ModelValidationException;
use app\models\billing\CallsRaw;
use app\models\billing\ServiceTrunk;
use app\models\billing\TrunkGroup;
use app\models\ClientContract;
use app\models\voip\filter\CallsRawFilter;
use app\modules\nnp\models\City;
use app\modules\nnp\models\FilterQuery;
use app\modules\nnp\models\Region;
use Yii;
use yii\filters\AccessControl;

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
                            'get-trunk-groups',
                            'save-filter',
                            'get-filters',
                            'get-saved-filter-data',
                            'with-cache',
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
     * @throws \yii\base\ExitException
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
     * @throws \yii\base\ExitException
     */
    public function actionGetContracts(array $serverIds = [], array $serviceTrunkIds = [], array $trunkIds = [])
    {
        ReturnFormatted::me()->returnFormattedValues(
            ClientContract::dao()->getListWithType(
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
     * @param array $trunkGroupIds
     * @param array $serviceTrunkIds
     * @param array $contractIds
     * @throws \yii\base\ExitException
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
     * Сохранение набора фильтров для отчета по calls_raw
     *
     * @return int
     * @throws ModelValidationException
     */
    public function actionSaveFilter()
    {
        $post = Yii::$app->request->post();

        if (!isset($post['name']) || !$post['name']) {
            return false;
        }

        $values = [
            'name' => $post['name'],
            'data' => json_encode(array_diff($post['CallsRawFilter'], ['filter_id', 'connect_time_from', 'connect_time_to']), JSON_UNESCAPED_UNICODE),
            'model_name' => CallsRawFilter::className()
        ];

        $savedFilter = new FilterQuery();
        $savedFilter->load($values, '');

        if (!($result = $savedFilter->save())) {
            throw new ModelValidationException($savedFilter);
        }

        return $result;
    }

    /**
     * Вывести список сохраненных фильтров
     */
    public function actionGetFilters()
    {
        ReturnFormatted::me()->returnFormattedValues(
            FilterQuery::getList(
                $isWithEmpty = true,
                $isWithNullAndNotNull = false,
                $indexBy = 'id',
                $select = 'name',
                $orderBy = ['name' => SORT_ASC],
                $where = ['model_name' => CallsRawFilter::className()]
            ),
            'options'
        );
    }

    /**
     * Получить параметры фильтра
     *
     * @param null $id
     * @return array|bool
     */
    public function actionGetFilterQueryData($id = null)
    {
        if (!$id || !($filter = FilterQuery::findOne(['id' => $id]))) {
            return false;
        }

        return $filter->data;
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
     * Метод, не поддерживающий кеширование
     *
     * @return string
     */
    public function actionIndex()
    {
        CallsRaw::getDb()
            ->createCommand("set work_mem = '500MB'")->execute();

        $model = new CallsRawFilter;
        $model->load(Yii::$app->request->get());

        $params = [
            'filterModel' => $model,
        ];
        return !isset(Yii::$app->request->get()['_pjax']) ?
            $this->render('index', $params) : $this->renderPartial('index', $params);
    }

    /**
     * Метод, поддерживающий кеширование
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionWithCache()
    {
        // Задаём объём памяти для внутренних операций
        CallsRaw::getDb()
            ->createCommand("set work_mem = '500MB'")->execute();
        // Получение фильтра
        $model = new CallsRawFilter;
        $model->load(Yii::$app->request->get());

        $params = [
            'filterModel' => $model,
            'isSupport' => Yii::$app->controller->action->id == 'with-cache',
            'isCache' => Yii::$app->getRequest()->get('isCache') == 1,
        ];
        return !isset(Yii::$app->request->get()['_pjax']) ?
            $this->render('index', $params) : $this->renderPartial('index', $params);
    }
}
