<?php

namespace app\classes;

use app\classes\model\ActiveRecord;
use app\controllers\CompatibilityController;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Region;
use app\models\Trouble;
use app\modules\uu\forms\CrudMultipleTrait;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class BaseController extends Controller
{
    use CrudMultipleTrait;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Инициализация
     */
    public function init()
    {
        parent::init();
        Language::setCurrentLanguage();
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (!($this instanceof CompatibilityController)) {
            $this->applyFixClient();
        }

        return \yii\base\Controller::beforeAction($action);
    }

    /**
     * @param bool $clientToFix
     */
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

    /**
     * @return NavigationBlock[]
     */
    public function getNavigationBlocks()
    {
        return Navigation::create()->getBlocks();
    }

    /**
     * @return int
     */
    public function getMyTroublesCount()
    {
        return Trouble::dao()->getMyTroublesCount();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $version = 0;
        try {
            $handle = fopen("../.helm/def.sh", "r");
            if ($handle) {
                $version = 1;
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'TAG=') !== false) {
                        $version = trim(substr($line, 4));
                        break;
                    }
                }
                fclose($handle);
            } else {
                $version = 2;
            }
        } catch (\Exception $e) {
            $version = 3;
        }

        return $version;
    }

    /**
     * @return array
     */
    public function getSearchData()
    {
        return [
            'filter' => $this->_getSearchDataFilter(),
            'regions' => $this->_getSearchDataRegions(),
            'currentFilter' => isset($_SESSION['letter']) ? $_SESSION['letter'] : false,
            'currentRegion' => isset($_SESSION['letter_region']) ? $_SESSION['letter_region'] : false,
            'clients_my' => isset($_SESSION['clients_my']) ? $_SESSION['clients_my'] : false,
            'module' => Yii::$app->request->get('module', 'clients'),
            'client_subj' => Yii::$app->request->get('subj', ''),
        ];
    }

    /**
     * @return array
     */
    private function _getSearchDataFilter()
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

    /**
     * @return array
     */
    private function _getSearchDataRegions()
    {
        $result = [
            'any' => '***Любой***',
            '99' => 'Москва',
        ];
        $regions = Region::find()
            ->select('id, name')
            ->asArray()
            ->all();
        foreach ($regions as $region) {
            $result[$region['id']] = $region['name'];
        }

        return $result;
    }

    /**
     * @param int $id
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

    /**
     * @return ClientAccount
     */
    public function getFixClient()
    {
        global $fixclient_data;
        if (isset($fixclient_data['id']) && $fixclient_data['id']) {
            $accountId = $fixclient_data['id'];
        } elseif (isset($_SESSION["clients_client"]) && $_SESSION["clients_client"]) {
            $accountId = $_SESSION["clients_client"];
        } elseif (Yii::$app->user->identity->restriction_client_id) {
            $accountId = Yii::$app->user->identity->restriction_client_id;
        } else {
            $accountId = null;
        }

        if ($accountId) {
            return ClientAccount::findOne($accountId);
        }

        return null;
    }

    /**
     * Формирует результат в формате PDF, по умолчанию отдает на отображение в браузер
     *
     * @param string $view
     * @param array $params
     * @param array $pdfParams
     * @return mixed
     */
    public function renderAsPDF($view, $params = [], $pdfParams = [])
    {
        $content = $this->renderPartial($view, $params + ['isPdf' => 1]);
        /*
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
                // 'SetHeader'=>[''],
                // 'SetFooter'=>['{PAGENO}'],
            ]
        ];

        $pdf = new \kartik\mpdf\Pdf(array_merge($pdfDefault, $pdfParams));

        return $pdf->render();
        */
        $generator = new Html2Pdf;
        $generator->html = $content;
        return $generator->pdf;
    }

    /**
     * Формирует результат в формате MHTML (Word2003) и отдает на скачивание
     *
     * @param string $view
     * @param array $params
     * @return bool
     * @throws \Exception
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
                $filePath = $fileName = '';

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

        Yii::$app->response->sendContentAsFile($result, isset($params['fileName']) ? $params['fileName'] : time() . Yii::$app->user->id . '.doc');
        Yii::$app->end();

        return false;
    }


    /**
     * Обработать submit (создать, редактировать, удалить)
     *
     * @param ActiveRecord $model
     * @param ActiveRecord[] $childModels
     * @param ActiveRecord $originalChild
     * @return bool|int
     * @throws \yii\db\Exception
     */
    protected function loadFromInput(ActiveRecord $model, &$childModels = null, $originalChild = null)
    {
        // загрузить параметры от юзера
        $transaction = $model::getDb()->beginTransaction();

        $isSuccess = false;
        try {
            $post = Yii::$app->request->post();

            if (isset($post['dropButton'])) {

                // удалить
                $model->delete();
                $isSuccess = true;
                Yii::$app->session->addFlash('success', 'Объект успешно удален.');

            } elseif ($post && $model->load($post) && $model->save()) {

                // сохранена основная модель
                //
                if ($originalChild) {
                    // сохранить модели детей
                    $originalChild->setParentId($model->primaryKey);
                    $childModels = $this->crudMultiple($childModels, $post, $originalChild);

                    if ($this->validateErrors) {
                        throw new \LogicException(implode('. ', $this->validateErrors));
                    }
                }

                $isSuccess = $model->getPrimaryKey();
                Yii::$app->session->addFlash('success', 'Объект успешно сохранен.');
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $isSuccess;
    }
}
