<?php
/**
 * Тип номера
 */

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\voip\forms\NumberTypeFormEdit;
use app\classes\voip\forms\NumberTypeFormNew;
use app\models\filter\NumberTypeFilter;
use Yii;
use yii\filters\AccessControl;

class NumberTypeController extends BaseController
{
    /**
     * Права доступа
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
                        'actions' => ['index'],
                        'roles' => ['voip.access'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['voip.access'],
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
        $filterModel = new NumberTypeFilter();
        $filterModel->load(Yii::$app->request->get());

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
        /** @var NumberTypeFormNew $form */
        $form = new NumberTypeFormNew();

        if ($form->isSaved) {
            // создали
            return $this->redirect([
                'edit',
                'id' => $form->id,
            ]);
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
        /** @var NumberTypeFormEdit $form */
        $form = new NumberTypeFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {

            if ($form->id) {
                // отредактировали
                return $this->redirect([
                    'edit',
                    'id' => $form->id,
                ]);
            } else {
                // удалили
                return $this->redirect([
                    'index',
                ]);
            }
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}