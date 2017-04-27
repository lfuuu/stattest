<?php
namespace app\controllers\voip;

use app\forms\voip\RegistryForm;
use app\models\City;
use app\models\voip\Registry;
use Yii;
use app\models\filter\voip\RegistryFilter;
use app\classes\BaseController;
use yii\filters\AccessControl;

class RegistryController extends BaseController
{

    /**
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
                        'actions' => ['index', 'edit', 'add', 'delete'],
                        'roles' => ['voip.admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new RegistryFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Действие контроллера. Добавление.
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
       return $this->actionEdit(0);
    }

    /**
     * Действие контроллера. Редактирование.
     *
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        /** @var Registry $registry */
        $registry = Registry::findOne(['id' => $id]);

        $model = new RegistryForm;

        if (Yii::$app->request->post('save')) {
            $model->setScenario('save');
        }

        if ($registry) {
            $model->initModel($registry);
        }

        $isLoad = $model->load(Yii::$app->request->post());

        if ($isLoad && $model->initForm($isFromPost = true)) {
            if ($model->getScenario() == 'save' && $model->validate() && $model->save()) {
                Yii::$app->session->addFlash('success', ($id ? 'Запись обновлена' : 'Запись создана'));
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }

        $isShowCheckList = false;
        $checkList = null;
        $statusInfo = [];
        try {
            if (Yii::$app->request->post('fill-numbers')) {
                $registry->fillNumbers();
                City::dao()->markUseCities();
                $isShowCheckList = true;
            }

            if (Yii::$app->request->post('to-sale')) {
                $registry->toSale();
                $isShowCheckList = true;
            }

            if ($isShowCheckList || Yii::$app->request->post('check-numbers')) {
                $checkList = $registry->getPassMap();
                $isShowCheckList = true;
            }

            if ($isShowCheckList) {
                $statusInfo = $registry->getStatusInfo();
            }
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        $model->initForm();

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
            'checkList' => $checkList,
            'statusInfo' => $statusInfo
        ]);
    }

    /**
     * Действие контроллера. Удаление записи.
     *
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (($id = Yii::$app->request->post('id')) && ($registry = Registry::findOne(['id' => $id]))) {
            Yii::$app->session->addFlash('success', 'Запись удалена');
            $registry->delete();
        }

        return $this->redirect(['voip/registry']);
    }
}
