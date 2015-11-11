<?php
namespace app\controllers\document;

use app\classes\BaseController;
use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;
use yii\filters\AccessControl;

class TemplateController extends BaseController
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

    public function actionEdit($id = false)
    {
        $model = DocumentTemplate::findOne($id);
        if(!$model)
            $model = new DocumentTemplate();
        $request = \Yii::$app->request->post('DocumentTemplate');
        if($request) {
            $model->content = preg_replace_callback(
                '#\{[^\}]+\}#',
                function($matches) {
                    return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
                },
                $request['content']
            );
            $model->save();
        }
        return $this->render('edit', ['model' => $model]);
    }

    public function actionEditForm($id = false)
    {
        $model = DocumentTemplate::findOne($id);
        if (!$model)
            $model = new DocumentTemplate();

        $request = \Yii::$app->request->post();
        if($request) {
            if(!empty($request['DocumentTemplate']['folder_name'])){
                $folder = new DocumentFolder();
                $folder->name = $request['DocumentTemplate']['folder_name'];
                $folder->sort = 0;
                $folder->save();
                $request['DocumentTemplate']['folder_id'] = $folder->id;
            }

            if ($model->load($request) && $model->save())
                \Yii::$app->session->setFlash('success');
        }
        $this->layout = 'minimal';
        return $this->render('short', ['model' => $model]);
    }
}