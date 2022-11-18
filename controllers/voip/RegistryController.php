<?php

namespace app\controllers\voip;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\forms\voip\RegistryForm;
use app\models\City;
use app\models\filter\voip\RegistryFilter;
use app\models\Number;
use app\models\voip\Registry;
use Exception;
use Yii;
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
                'class' => AccessControl::class,
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
     * @throws \yii\base\InvalidParamException
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
     * @throws \yii\base\InvalidParamException
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
     * @throws ModelValidationException
     */
    public function actionEdit($id)
    {
        $post = Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();

        /** @var Registry $registry */
        $registry = Registry::findOne(['id' => $id]);

        $model = new RegistryForm;

        $submitName = !$registry || ($registry && $registry->isEditable() && $registry->isSubmitable()) ? 'save' : 'save-comment';

        if ($post['save-comment'] && ($comment = trim($post['RegistryForm']['comment']))) {
            $registry->comment = $comment;
            if (!$registry->save()) {
                throw new ModelValidationException($registry);
            }
            return $this->redirect(['edit', 'id' => $registry->id]);
        }
        if ($post['save']) {
            $model->setScenario('save');
        }

        if ($registry) {
            $model->initModel($registry);
        }

        $isLoad = $model->load($post);

        $isSave = $model->getScenario() === 'save';

        $isPost = \Yii::$app->request->isPost;

        if ($isSave && $isLoad && $model->initForm($isPost) && $model->validate() && $model->save()) {
            Yii::$app->session->addFlash('success', ($id ? 'Запись обновлена' : 'Запись создана'));
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        $isShowCheckList = false;
        $checkList = null;
        $statusInfo = [];
        try {
            if ($post['fill-numbers']) {
                $registry->fillNumbers();
                City::dao()->markUseCities();
                $isShowCheckList = true;
            }

            if ($post['to-sale']) {
                $registry->toSale();
                $isShowCheckList = true;
            }

            if ($post['attach-to-registry']) {
                $registry->attachNumbers();
                $isShowCheckList = true;
            }

            if ($post['set-didgroup']) {
                $registry->setDidGroup();
                $isShowCheckList = true;
            }

        } catch (\LogicException $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
            $isShowCheckList = true;
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        if ($isShowCheckList || $post['check-numbers']) {
            $checkList = $registry->getPassMap();
            $isShowCheckList = true;
        }

        if ($isShowCheckList) {
            $statusInfo = $registry->getStatusInfo();
        }

        $model->initForm($isPost);

        return $this->render('edit', [
            'model' => $model,
            'creatingMode' => false,
            'checkList' => $checkList,
            'statusInfo' => $statusInfo,
            'submitName' => $submitName
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
