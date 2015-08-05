<?php
namespace app\controllers;

use app\classes\Assert;
use Yii;
use yii\data\ActiveDataProvider;
use app\classes\BaseController;
use yii\base\Exception;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\media\ClientFiles;
use app\models\media\TroubleFiles;
use app\models\ClientContract;

class FileController extends BaseController
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

    public function actionGetFile($model, $id)
    {
        switch ($model) {
            case 'clients':
                $file = ClientFiles::findOne($id);
                break;
            case 'troubles':
                $file = TroubleFiles::findOne($id);
                break;
        }

        Assert::isObject($file);

        $file->mediaManager->getContent($file);
    }

    public function actionList($contractId)
    {
        $model = ClientContract::findOne($contractId);
        if (null === $model)
            throw new Exception('Договор не найден');

        return $this->render('list', ['model' => $model]);
    }

    public function actionUploadClientFile($contractId, $childId = null)
    {
        $model = ClientContract::findOne($contractId);

        if (!$model)
            throw new Exception("Договор не найден");

        $request = Yii::$app->request->post();
        if (isset($_FILES['file'])) {
            $model->mediaManager->addFile($_FILES['file'], $request['comment'], $request['name']);
        }

        if ($childId)
            return $this->redirect(['client/view', 'id' => $childId]);
        else
            return $this->redirect(['file/list', 'contractId' => $contractId]);
    }

    public function actionDeleteClientFile($id)
    {
        $fileModel = ClientFiles::findOne($id);

        if (null === $fileModel)
            throw new Exception('Файл не найден');

        $fileModel->contract->mediaManager->removeFile($fileModel);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }

    public function actionSendClientFile($id)
    {
        $model = ClientFiles::findOne($id);

        if (null === $model)
            throw new Exception('Файл не найден');

        $res = [
            'file_name' => $model->name,
            'file_content' => base64_encode($model->content),
            'msg_session' => md5(rand() + time()),
            'file_mime' => $model->mime,
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionReport()
    {
        $request = Yii::$app->request->post();
        $query = ClientFiles::find()->orderBy(['ts' => SORT_DESC]);

        if($request['user_id'])
            $query->andWhere(['user_id' => $request['user_id']]);
        if($request['date_from'])
            $query->andWhere(['<=','ts', $request['date_from']]);
        if($request['date_to'])
            $query->andWhere(['>=','ts', $request['date_to']]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('report', ['dataProvider' => $dataProvider]);
    }
}

