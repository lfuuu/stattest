<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\modules\sim\filters\PhoneHistoryFilter;
use app\modules\sim\forms\porting\Form;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;

/**
 * Портирование
 */
class PortingController extends BaseController
{
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
                        'actions' => ['index'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['import', 'step2', 'step3'],
                        'roles' => ['sim.write'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new PhoneHistoryFilter();
        if ($sort = \Yii::$app->request->get('sort')) {
            $filterModel->sort = $sort;
        }
        if ($date = \Yii::$app->request->get('date')) {
            $filterModel->date = $date;
        }
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Импорт
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionImport()
    {
        if (isset($_FILES['file'])) {
            /** @var Form $form */
            $formModel = new Form();

            // загрузить файл
            $pathToCSV = $formModel->addFile($_FILES['file']);
            if (!$pathToCSV) {
                Yii::$app->session->addFlash('error', 'Ошибка загрузки файла');
                return $this->redirect(Url::to(['/sim/porting/import/']));
            }

            Yii::$app->session->addFlash('success', 'Файл загружен для импорта');
            return $this->redirect(Url::to(['/sim/porting/step2', 'path' => $pathToCSV]));
        }

        return $this->render('import', []);
    }

    /**
     * Импорт
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionStep2($path)
    {
        $isPost = Yii::$app->request->isPost;

        /** @var Form $form */
        $formModel = new Form([
            'path' => $path,
            'isToSave' => $isPost,
        ]);

        $errorMessage = '';
        if (!$formModel->runPorting()) {
            $errorMessage = $formModel->errorMessage ? : 'Ошибка импорта файла';
        }

        if ($isPost) {
            if ($errorMessage) {
                Yii::$app->session->addFlash('error', $errorMessage);
                return $this->redirect(Url::to(['/sim/porting/import/']));
            }

            Yii::$app->session->addFlash('success', 'Файл успешно импортирован');
            return $this->redirect(Url::to(['/sim/porting/step3', 'count' => $formModel->getCount()]));
        }


        return $this->render('step2', [
            'path' => $path,
            'errorMessage' => $errorMessage,
            'warnings' => $formModel->warningLines,
        ]);
    }

    /**
     * Импорт
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionStep3($count)
    {
        return $this->render('step3', [
            'count' => $count,
        ]);
    }
}
