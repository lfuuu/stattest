<?php

namespace app\controllers\dictionary;

use Yii;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use app\models\CityBillingMethod;
use app\forms\dictonary\city_billing_method\CityBillingMethodNew;
use app\forms\dictonary\city_billing_method\CityBillingMethodEdit;

class CityBillingMethodsController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => CityBillingMethod::find(),
                'sort' => false,
            ])
        ]);
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $form = new CityBillingMethodNew;

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'formModel' => $form,
        ]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionEdit($id)
    {
        $form = new CityBillingMethodEdit([
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