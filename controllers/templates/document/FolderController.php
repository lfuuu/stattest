<?php
namespace app\controllers\templates\document;

use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\document\DocumentFolder;

class FolderController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('list');
    }

    public function actionEdit($id = false)
    {
        $model = DocumentFolder::findOne($id);

        if (!$model) {
            $model = new DocumentFolder;
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success');
            Yii::$app->response->redirect('/templates/document/template');
        }

        return $this->render('edit', ['model' => $model]);
    }

}