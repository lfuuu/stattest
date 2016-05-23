<?php
namespace app\controllers\tariff;

use app\classes\BaseController;
use app\forms\tariff\DidGroupForm;
use app\models\DidGroup;
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

    public function actionAdd($city_id = null)
    {
        return $this->actionEdit(null, $city_id);
    }

    public function actionEdit($id = null, $city_id = null)
    {
        $didGroup = null;
        $form = new DidGroupForm();

        $city_id = (int) $city_id;
        $id = (int) $id;

        if ($city_id) {
            $form->city_id = $city_id;
        }

        if ($id) {
            $didGroup = DidGroup::findOne(['id' => $id]);
            $form->initModel($didGroup);
        }

        if (Yii::$app->request->post('save')) {
            $form->setScenario('save');
        }

        if ($form->load(Yii::$app->request->post()) && $form->getScenario() == 'save' && $form->validate() && $form->save()) {
            Yii::$app->session->addFlash('success', ($id ? 'Запись обновлена' : 'Запись создана'));
            return $this->redirect(['edit', 'id' => $form->id]);
        }

        $form->initForm();

        return $this->render('edit', [
            'model' => $form,
        ]);
    }
}
