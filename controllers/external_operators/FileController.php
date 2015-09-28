<?php
namespace app\controllers\external_operators;

use Yii;
use yii\filters\AccessControl;
use app\classes\Assert;
use app\classes\BaseController;
use app\models\media\TroubleFiles;

class FileController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['get-file'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionGetFile($model, $id)
    {
        switch ($model) {
            case 'troubles':
                $file = TroubleFiles::findOne($id);
                break;
        }

        Assert::isObject($file);

        $file->mediaManager->getContent($file);
    }

}