<?php
/**
 * Номера
 */

namespace app\controllers\voip;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\voip\forms\NumberFormEdit;
use app\classes\voip\forms\NumberFormNew;
use app\models\Country;
use app\models\filter\voip\NumberFilter;
use app\models\NumberType;
use Yii;

class NumberController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Вернуть имя колонки, в которую надо установить фильтр по клиенту
     * @return string
     */
    protected function getClientAccountField()
    {
        return 'client_id';
    }

    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new NumberFilter();

        $get = Yii::$app->request->get();
        $className = $filterModel->formName();
        !isset($get[$className]['country_id']) && $get[$className]['country_id'] = Country::RUSSIA;
        !isset($get[$className]['number_type']) && $get[$className]['number_type'] = NumberType::ID_GEO_DID;

        $this->addClientAccountFilter($filterModel, $get);

        $post = Yii::$app->request->post();
        if (isset($post['Number'])) {
            $filterModel->groupEdit($post['Number']);
        }

        return $this->render('index', [
            'filterModel' => $filterModel,
            'currentClientAccountId' => $this->getCurrentClientAccountId(),
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