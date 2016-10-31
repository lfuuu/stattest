<?php
namespace app\controllers\tariff;

use app\classes\BaseController;
use app\forms\tariff\TariffNumberAddForm;
use app\forms\tariff\TariffNumberEditForm;
use app\models\filter\TariffNumberFilter;
use app\models\TariffNumber;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

class NumberController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['tarifs.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new TariffNumberFilter();
        $filterModel->load(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     */
    public function actionAdd()
    {
        $model = new TariffNumberAddForm;
        $model->activation_fee = 0;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario === 'save' && $model->save()) {
                Yii::$app->session->addFlash('success', 'Запись добавлена');
                $this->redirect('index');
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => true,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        $model = new TariffNumberEditForm;

        $tariff = TariffNumber::findOne($id);
        if ($tariff === null) {
            throw new BadRequestHttpException();
        }
        $model->setAttributes($tariff->getAttributes(), false);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->scenario === 'save' && $model->save()) {
                Yii::$app->session->addFlash('success', 'Запись сохранена');
                $this->redirect('index');
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
        ]);
    }
}
