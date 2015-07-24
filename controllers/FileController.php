<?php
namespace app\controllers;

use yii\data\ActiveDataProvider;
use app\models\ClientContract;
use app\models\ClientFile;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\web\Response;
use yii\filters\AccessControl;

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

    public function actionList($contractId)
    {
        $model = ClientContract::findOne($contractId);
        if (null === $model)
            throw new Exception('Договор не найден');

        return $this->render('list', ['model' => $model]);
    }

    public function actionDownload($id)
    {
        $model = ClientFile::findOne($id);

        if (null === $model)
            throw new Exception('Файл не найден');

        header("Content-Type: " . $model->mime);
        header("Pragma: ");
        header("Cache-Control: ");
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . iconv("UTF-8", "CP1251", $model->name) . '"');
        header("Content-Length: " . strlen($model->content));
        echo $model->content;
        die;
    }

    public function actionUpload($contractId, $childId = null)
    {
        $model = ClientContract::findOne($contractId);

        if (!$model)
            throw new Exception("Договор не найден");

        $request = Yii::$app->request->post();
        $model->fileManager->addFile($request['comment'], $request['name']);

        if ($childId)
            return $this->redirect(['client/view', 'id' => $childId]);
        else
            return $this->redirect(['file/list', 'contractId' => $childId]);
    }

    public function actionDelete($id)
    {
        $model = ClientFile::findOne($id);

        if (null === $model)
            throw new Exception('Файл не найден');

        $model->contract->fileManager->removeFile($id);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }

    public function actionSend($id)
    {
        $model = ClientFile::findOne($id);

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
        $query = ClientFile::find()->orderBy(['ts' => SORT_DESC]);

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

