<?php

namespace app\classes\media;

use Yii;
use DateTime;
use app\classes\model\ActiveRecord;
use app\models\ClientContract;
use app\models\media\ClientFiles;
use app\helpers\DateTimeZoneHelper;

class ClientMedia extends MediaManager
{

    /** @var ClientContract */
    private $_contract;

    /**
     * @param ClientContract $contract
     */
    public function __construct(ClientContract $contract)
    {
        $this->_contract = $contract;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'files';
    }

    /**
     * @param string $name
     * @param string $content
     * @param string $comment
     * @param int $userId
     * @param bool $isShowInLk
     * @return ClientFiles|bool
     * @throws \Exception
     */
    public function addFileFromParam($name, $content, $comment = '', $userId = null, $isShowInLk = false)
    {
        if (!$name) {
            throw new \Exception('Не задано имя файла');
        }

        if (!$userId) {
            $userId = Yii::$app->user->getId();
        }

        $model = $this->createFileModel($name, $comment, $userId, $isShowInLk);

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
     * @param string $name
     * @param string $comment
     * @param int $userId
     * @param bool $isShowInLk
     * @return ClientFiles
     */
    protected function createFileModel($name, $comment, $userId = null, $isShowInLk = false)
    {
        $model = new ClientFiles;
        $model->contract_id = $this->_contract->id;
        $model->ts = (new DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $model->name = $name;
        $model->comment = $comment;
        $model->user_id = $userId ?: Yii::$app->user->getId();
        $model->is_show_in_lk = $isShowInLk;

        $model->save();

        return $model;
    }

    /**
     * @param ActiveRecord $fileModel
     * @throws \Exception
     */
    protected function deleteFileModel(ActiveRecord $fileModel)
    {
        /** @var ClientFiles $model */
        $model = ClientFiles::findOne(['contract_id' => $this->_contract->id, 'id' => $fileModel->id]);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * @return ClientFiles[]
     */
    protected function getFileModels()
    {
        return ClientFiles::findAll(['contract_id' => $this->_contract->id]);
    }

}
