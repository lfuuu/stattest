<?php
namespace app\controllers;

use app\models\ClientAccount;
use app\models\ClientFile;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\Response;

class FileController extends BaseController
{
    public function actionList($userId)
    {
        $model = ClientAccount::findOne($userId);
        if (null === $model)
            throw new Exception('Клиент не найден');

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

    public function actionUpload($userId)
    {
        $model = ClientAccount::findOne($userId);

        if (!$model)
            throw new Exception("ЛС не найден");

        $request = Yii::$app->request->post();
        $model->fileManager->addFile($request['comment'], $request['name']);

        return $this->redirect(Url::toRoute(['file/list', 'userId'=> $userId]));
    }

    public function actionDelete($id)
    {
        $model = ClientFile::findOne($id);

        if (null === $model)
            throw new Exception('Файл не найден');

        $model->client->fileManager->removeFile($id);

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
            'msg_session' => md5(rand()+time()),
            'file_mime' => $model->mime,
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }
}

