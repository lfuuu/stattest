<?php
/**
 * Номера
 */

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\voip\forms\NumberFormEdit;
use app\classes\voip\forms\NumberFormNew;
use app\models\filter\voip\NumberFilter;
use Yii;

class NumberController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new NumberFilter();
        $filterModel->load(Yii::$app->request->get());

        $post = Yii::$app->request->post();
        if (isset($post['Number'])) {
            $filterModel->groupEdit($post['Number']);
        }

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
        /** @var NumberFormNew $form */
        $form = new NumberFormNew();

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
        /** @var NumberFormEdit $form */
        $form = new NumberFormEdit([
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