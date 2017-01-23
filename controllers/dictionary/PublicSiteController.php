<?php

namespace app\controllers\dictionary;

use Yii;
use app\classes\BaseController;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use app\models\dictionary\PublicSite;

class PublicSiteController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PublicSite::find(),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws InvalidParamException
     */
    public function actionEdit($id = 0)
    {
        if ($id) {
            if (!($model = PublicSite::findOne(['id' => $id]))) {
                throw new InvalidParamException;
            }

            $model->loadDefaultValues();
        } else {
            $model = new PublicSite;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/dictionary/public-site');
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @throws InvalidParamException
     */
    public function actionDelete($id)
    {
        if (!($model = PublicSite::findOne(['id' => $id]))) {
            throw new InvalidParamException;
        }

        $model->delete();

        $this->redirect('/dictionary/public-site');
    }

}