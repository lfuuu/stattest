<?php
namespace app\controllers\templates\document;

use app\classes\BaseController;
use app\models\document\DocumentTemplate;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;

class TemplateController extends BaseController
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
                        'roles' => ['clients.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        return $this->render('list');
    }

    /**
     * @param bool|false $id
     * @return string
     * @throws InvalidParamException
     */
    public function actionEdit($id = false)
    {
        $model = DocumentTemplate::findOne($id);

        if (!$model) {
            $model = new DocumentTemplate;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Документ успешно сохранен');
            Yii::$app->response->redirect('/templates/document/template');
        }

        return $this->render('edit', [
            'model' => $model
        ]);
    }

}