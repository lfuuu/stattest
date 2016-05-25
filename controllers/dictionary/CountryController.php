<?php
/**
 * Страны
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\dictionary\forms\CountryFormEdit;
use app\classes\dictionary\forms\CountryFormNew;
use app\models\filter\CountryFilter;
use Yii;

class CountryController extends BaseController
{
    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new CountryFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['CountryFilter'])) {
            $get['CountryFilter']['in_use'] = 1; // по-умолчанию только "вкл."
        }
        $filterModel->load($get);

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
        /** @var CountryFormNew $form */
        $form = new CountryFormNew();

        if ($form->isSaved) {
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
        /** @var CountryFormEdit $form */
        $form = new CountryFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }
}