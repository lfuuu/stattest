<?php
namespace app\controllers\tariff;

use app\classes\BaseController;
use app\forms\tariff\DidGroupFormEdit;
use app\forms\tariff\DidGroupFormNew;
use app\models\filter\DidGroupFilter;
use Yii;
use yii\filters\AccessControl;

class DidGroupController extends BaseController
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
        $filterModel = new DidGroupFilter();
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
    public function actionNew()
    {
        /** @var DidGroupFormNew $form */
        $form = new DidGroupFormNew();

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        /** @var DidGroupFormEdit $form */
        $form = new DidGroupFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}
