<?php

namespace app\classes\media;

use app\models\ClientContract;
use Yii;
use DateTime;
use app\models\media\ClientFiles;

class ClientMedia extends MediaManager
{
    /** @var ClientContract */
    private $contract;

    public function __construct(ClientContract $contract)
    {
        $this->contract = $contract;
    }

    public function getFolder()
    {
        return 'files';
    }

    /**
     * @return ClientFiles
     */
    protected function createFileModel($name, $comment)
    {
        $model = new ClientFiles();
        $model->contract_id = $this->contract->id;
        $model->ts = (new DateTime())->format(DateTime::ATOM);

        $model->name = $name;
        $model->comment = $comment;
        $model->user_id = Yii::$app->user->getId();

        $model->save();

        return $model;
    }

    protected function deleteFileModel($fileId)
    {
        /** @var ClientFiles $model */
        $model = ClientFiles::findOne(['contract_id' => $this->contract->id, 'id' => $fileId]);
        if ($model) {
            $model->delete();
        }
    }

    protected function getFileModels()
    {
        return ClientFiles::findAll(['contract_id' => $this->contract->id]);
    }


}
