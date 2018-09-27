<?php
/**
 * Страны
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\dictionary\forms\CountryFormEdit;
use app\models\filter\CountryFilter;
use Yii;
use yii\filters\AccessControl;

class CountryController extends BaseController
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
                        'actions' => ['edit'],
                        'roles' => ['dictionary.country'],
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
        $filterModel = new CountryFilter();

        $get = Yii::$app->request->get();
        if (!isset($get['CountryFilter'])) {
            $get['CountryFilter']['in_use'] = 1; // по умолчанию только "вкл."
        }

        $filterModel->load($get);

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
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
        /** @var CountryFormEdit $form */
        $form = new CountryFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'formModel' => $form,
        ]);
    }
}