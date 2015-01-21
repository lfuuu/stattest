<?php
namespace app\classes;

use app\models\Bill;
use app\models\Trouble;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use app\models\Region;
use yii\web\NotFoundHttpException;

class BaseController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        return \yii\base\Controller::beforeAction($action);
    }

    /**
     * @return NavigationBlock[]
     */
    public function getNavigationBlocks()
    {
        return Navigation::create()->getBlocks();
    }

    public function getMyTroublesCount()
    {
        return Trouble::dao()->getMyTroublesCount();
    }

    public function getSearchData()
    {
        return [
            'filter' => $this->getSearchDataFilter(),
            'regions' => $this->getSearchDataRegions(),
            'currentFilter' => isset($_SESSION['letter']) ? $_SESSION['letter'] : false,
            'currentRegion' => isset($_SESSION['letter_region']) ? $_SESSION['letter_region'] : false,
            'clients_my' => isset($_SESSION['clients_my']) ? $_SESSION['clients_my'] : false,
            'module' => Yii::$app->request->get('module', 'clients'),
            'client_subj' => Yii::$app->request->get('subj', ''),
        ];
    }

    /**
     * @param $id
     * @return Bill
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function getBillOr404($id)
    {
        if (!$id) {
            throw new BadRequestHttpException();
        }

        $result = Bill::findOne($id);

        if ($result === null) {
            throw new NotFoundHttpException();
        }

        return $result;
    }

    private function getSearchDataFilter()
    {
        return [
            '' => '***нет***',
            '*' => '*',
            '@' => '@',
            '!' => 'Клиенты ItPark',
            '+' => 'Тип: Дистрибютор',
            '-' => 'Тип: Оператор',
            'firma:mcn_telekom' => 'ООО "МСН Телеком"',
            'firma:mcn' => 'ООО "Эм Си Эн"',
            'firma:markomnet_new' => 'ООО "МАРКОМНЕТ"',
            'firma:markomnet_service' => 'ООО "МАРКОМНЕТ сервис"',
            'firma:ooomcn' => 'ООО "МСН"',
            'firma:all4net' => 'ООО "ОЛФОНЕТ"',
            'firma:ooocmc' => 'ООО "Си Эм Си"',
            'firma:mcm' => 'ООО "МСМ"',
            'firma:all4geo' => 'ООО "Олфогео"',
            'firma:wellstart' => 'ООО "Веллстарт"',
        ];
    }

    private function getSearchDataRegions()
    {
        $result = [
            'any' => '***Любой***',
            '99' => 'Москва',
        ];
        $regions =
            Region::find()
                ->select('id, name')
                ->asArray()
                ->all();
        foreach ($regions as $region) {
            $result[$region['id']] = $region['name'];
        }

        return $result;
    }
}