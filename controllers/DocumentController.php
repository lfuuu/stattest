<?php
namespace app\controllers;

use app\models\ClientAccount;
use app\models\ClientDocument;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\filters\AccessControl;

class DocumentController extends BaseController
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

    public function actionActivate($id)
    {
        $model = ClientDocument::findOne($id);
        if (!$model)
            throw new Exception('Document not found');

        $model->is_active = !$model->is_active;
        $model->save();
        $this->redirect(Yii::$app->request->referrer);
    }

    public function actionCreate($id)
    {
        $content = Yii::$app->request->post('contract_content');
        $contractType = Yii::$app->request->post('contract_type');
        $contractGroup = Yii::$app->request->post('contract_template_group');
        $contractTemplate = Yii::$app->request->post('contract_template');
        $contractDate = Yii::$app->request->post('contract_date');
        $contractNo = Yii::$app->request->post('contract_no');
        $comment = Yii::$app->request->post('comment');


        $contractId = ClientDocument::dao()->addContract(
            $id,
            $contractType,
            $contractGroup,
            $contractTemplate,
            $contractNo,
            $contractDate,
            $content,
            $comment
        );

        if ($contractId && $contractType == 'contract') {
            $model = ClientAccount::findOne($id)->getContract();
            $model->number = $contractNo;
            $model->save();
        }

        $this->redirect(['client/view', 'id' => $id]);
    }

    /*
        public function actionEdit($id)
        {
            $model = ClientDocument::findOne($id);
            if(null === $model)
                throw new Exception('Документ не найден');

            $request = Yii::$app->request->post();

            if (isset($request)) {
                ClientDocument::dao()->addContract(
                    $model->client_id,
                    $model->type,
                    $request['contract_template_group'],
                    $request['contract_template'],
                    $request['contract_no'],
                    $request['contract_date'],
                    $request['contract_content'],
                    $request['comment']
                );

                return $this->redirect(Url::toRoute(['client/view', ['id' => $id]]));

            } else {
                $content = ClientDocument::dao()->getTemplate($model->client_id . '-' . $model->id);
                return $this->render('edit', ['model'=>$model, 'content' => $content]);
            }
        }
    */
}