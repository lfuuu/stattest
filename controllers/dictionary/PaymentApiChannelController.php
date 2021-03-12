<?php
/**
 * Регионы
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\dictionary\forms\PaymentApiChannelFormEdit;
use app\classes\dictionary\forms\PaymentApiChannelFormNew;
use app\classes\dictionary\forms\RegionFormEdit;
use app\classes\dictionary\forms\RegionFormNew;
use app\models\filter\PaymentApiChannelFilter;
use app\models\filter\RegionFilter;
use Yii;
use yii\filters\AccessControl;

class PaymentApiChannelController extends BaseController
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
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['dictionary.region'],
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
        $filterModel = new PaymentApiChannelFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var PaymentApiChannelFormNew $form */
        $form = new PaymentApiChannelFormNew();

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
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        /** @var PaymentApiChannelFormEdit $form */
        $form = new PaymentApiChannelFormEdit([
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