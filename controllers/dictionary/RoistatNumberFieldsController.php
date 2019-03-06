<?php
namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\forms\dictonary\roistat\RoistatNumberFieldsForm;
use app\models\RoistatNumberFields;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class RoistatNumberFieldsController extends BaseController
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
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => RoistatNumberFields::find()
                ->orderBy(['id' => SORT_DESC])
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Создать
     *
     * @return string|\yii\web\Response
     * @throws ModelValidationException
     */
    public function actionCreate($id = null)
    {
        $model = $id ? RoistatNumberFieldsForm::findOne(['id' => $id]) : new RoistatNumberFieldsForm();
        $post = Yii::$app->request->post();
        if ($post && $model->load($post, '')) {
            $model->fields = json_encode($model->fields_arr);
            try {
                if (!$model->save()) {
                    throw new ModelValidationException($model);
                }
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
            return $this->redirect('index');
        }

        return $this->render('edit', ['model' => $model]);
    }

    /**
     * Удалить
     *
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = RoistatNumberFields::findOne(['id' => $id]);
        if (!$model) {
            throw new Exception('Модель не найдена');
        }
        try {
            if (!$model->delete()) {
                throw new ModelValidationException($model);
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect('index');
    }
}
