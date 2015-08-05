<?php

namespace app\classes\media;

use Yii;
use DateTime;
use yii\db\ActiveRecord;
use app\models\ClientContract;
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

    public function addFileFromParam($name, $content, $comment = '', $userId = null)
    {
        if (!$name)
            throw new \Exception('Не задано имя файла');

        if (!$userId) {
            $userId = Yii::$app->user->getId();
        }

        $model = $this->createFileModel($name, $comment);

        if ($model->user_id !== $userId) {
            $model->user_id = $userId;
            $model->save();
        }

        if (file_put_contents($this->getFilePath($model), $content) !== false) {
            return $model;
        }

        return false;
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

    protected function deleteFileModel(ActiveRecord $file)
    {
        /** @var ClientFiles $model */
        $model = ClientFiles::findOne(['contract_id' => $this->contract->id, 'id' => $file->id]);
        if ($model) {
            $model->delete();
        }
    }

    protected function getFileModels()
    {
        return ClientFiles::findAll(['contract_id' => $this->contract->id]);
    }

}
