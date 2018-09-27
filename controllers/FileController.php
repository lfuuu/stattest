<?php

namespace app\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\models\ClientContract;
use app\models\media\ClientFiles;
use app\models\media\TroubleFiles;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Response;

class FileController extends BaseController
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
                        'actions' => ['get-file'],
                        'roles' => ['client.read', 'tt.view'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['list', 'send-client-file'],
                        'roles' => ['clients.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['report', 'upload-client-file'],
                        'roles' => ['clients.edit', 'clients.file'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete-client-file'],
                        'roles' => ['clients.can_delete_contract_documents'],
                    ]
                ],
            ],
        ];
    }

    /**
     * @param string $model
     * @param int $id
     * @throws Exception
     * @throws \yii\base\ExitException
     * @throws \yii\web\HttpException
     */
    public function actionGetFile($model, $id)
    {
        switch ($model) {
            case 'clients':
                /** @var ClientFiles $file */
                $file = ClientFiles::findOne($id);
                break;
            case 'troubles':
                /** @var TroubleFiles $file */
                $file = TroubleFiles::findOne($id);
                break;
            default:
                $file = null;
                break;
        }

        Assert::isObject($file);

        $file->mediaManager->getContent($file);
    }

    /**
     * @param int $contractId
     * @return Response
     * @throws Exception
     */
    public function actionUploadClientFile($contractId)
    {
        /** @var ClientContract $model */
        $model = ClientContract::findOne($contractId);

        if (!$model) {
            throw new Exception('Договор не найден');
        }

        $request = Yii::$app->request->post();
        if (isset($_FILES['file'])) {
            $model->mediaManager->addFile($_FILES['file'], $request['comment'], $request['name']);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function actionDeleteClientFile($id)
    {
        $fileModel = ClientFiles::findOne($id);

        if (null === $fileModel) {
            throw new Exception('Файл не найден');
        }

        $fileModel->contract->mediaManager->removeFile($fileModel);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'ok'];
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function actionSendClientFile($id)
    {
        $model = ClientFiles::findOne($id);

        if (null === $model) {
            throw new Exception('Файл не найден');
        }

        $file = $model->mediaManager->getFile($model, $with_content = 1);

        $res = [
            'file_name' => $model->name,
            'file_content' => base64_encode($file['content']),
            'msg_session' => md5(mt_rand() + time()),
            'file_mime' => $file['mimeType'],
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    /**
     * @return string
     */
    public function actionReport()
    {
        $request = Yii::$app->request->post();
        $query = ClientFiles::find()->orderBy(['ts' => SORT_DESC]);

        if ($request['user_id']) {
            $query->andWhere(['user_id' => $request['user_id']]);
        }

        if ($request['date_from']) {
            $query->andWhere(['<=', 'ts', $request['date_from']]);
        }

        if ($request['date_to']) {
            $query->andWhere(['>=', 'ts', $request['date_to']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('report', ['dataProvider' => $dataProvider]);
    }
}

