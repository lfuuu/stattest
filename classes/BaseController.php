<?php
namespace app\classes;

use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\controllers\CompatibilityController;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Trouble;
use app\models\Region;
use kartik\mpdf\Pdf;

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

    public function init()
    {
        parent::init();
        Language::setCurrentLanguage();
    }

    public function beforeAction($action)
    {
        if (!($this instanceof CompatibilityController)) {
            $this->applyFixClient();
        }
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

    protected function applyFixClient($clientToFix = false)
    {
        global $fixclient, $fixclient_data;

        $fixclient = $clientToFix;
        if (!$fixclient) {
            Yii::$app->session->open();
            $fixclient = isset($_SESSION['clients_client']) ? $_SESSION['clients_client'] : '';
        }
        $param = (is_numeric($fixclient)) ? $fixclient : ['client' => $fixclient];

        $fixclient_data = ClientAccount::findOne($param);

        if ($fixclient_data) {
            $fixclient = $fixclient_data->id;
        } else {
            $fixclient_data = [];
        }
    }

    public function getFixClient()
    {
        global $fixclient_data;
        if ($fixclient_data['id']) {
            $accountId = $fixclient_data['id'];
        } elseif ($_SESSION["clients_client"]) {
            $accountId = $_SESSION["clients_client"];
        } elseif (Yii::$app->user->identity->restriction_client_id) {
            $accountId = Yii::$app->user->identity->restriction_client_id;
        }
        if ($accountId) {
            return ClientAccount::findOne($accountId);
        }
        return null;
    }

    /**
     * Формирует результат в формате PDF, по-умолчанию отдает на отображение в браузер
     *
     * @param string $view
     * @param [] $params
     * @param [] $pdfParams
     */
    public function renderAsPDF($view, $params = [], $pdfParams = [])
    {
        $this->layout = 'empty';
        $content = parent::render($view, $params + ['isPdf' => 1]);

        $pdfDefault = [
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[''],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ];

        $pdf = new \kartik\mpdf\Pdf(array_merge($pdfDefault, $pdfParams));

        return $pdf->render();
    }

    /**
     * Формирует результат в формате MHTML (Word2003) и отдает на скачивание
     *
     * @param string $view
     * @param [] $params
     */
    public function renderAsMHTML($view, $params = [])
    {
        $this->layout = 'empty';
        $content = parent::render($view, $params);

        $result = (new Html2Mhtml)
            ->addContents(
                'index.html',
                $content,
                function ($content) {
                    return preg_replace('#font\-size:\s?[0-7]{1,2}\%#', 'font-size:8pt', $content);
                }
            )
            ->addImages(function ($imageSrc) {
                $filePath =
                $fileName = '';

                if (preg_match('#\/[a-z]+(?![\.a-z]+)\?.+?#i', $imageSrc)) {
                    $fileName = 'host_img_' . mt_rand(0, 50);
                    $filePath = Yii::$app->request->hostInfo . $imageSrc;
                } else {
                    if (strpos($imageSrc, 'http:\/\/') === false) {
                        $filePath = Yii::$app->basePath . '/web' . $imageSrc;
                        $fileName = basename($imageSrc);
                    }
                }

                return [$fileName, $filePath];
            })
            ->getFile();

        Yii::$app->response->sendContentAsFile($result, time() . Yii::$app->user->id . '.doc');
        Yii::$app->end();

        return false;
    }

}
