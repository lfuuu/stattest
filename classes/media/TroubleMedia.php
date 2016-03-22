<?php

namespace app\classes\media;

use Yii;
use DateTime;
use yii\db\ActiveRecord;
use app\models\Trouble;
use app\models\media\TroubleFiles;

class TroubleMedia extends MediaManager
{
    /** @var Trouble */
    private $trouble;

    /**
     * @param Trouble $trouble
     */
    public function __construct(Trouble $trouble)
    {
        $this->trouble = $trouble;
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return 'files/troubles';
    }

    /**
     * @param string $name
     * @param string $comment
     * @return TroubleFiles
     */
    protected function createFileModel($name, $comment)
    {
        $model = new TroubleFiles;
        $model->trouble_id = $this->trouble->id;
        $model->ts = (new DateTime())->format(DateTime::ATOM);

        $model->name = $name;
        $model->user_id = Yii::$app->user->getId();

        $model->save();

        return $model;
    }

    /**
     * @param ActiveRecord $fileModel
     * @throws \Exception
     */
    protected function deleteFileModel(ActiveRecord $fileModel)
    {
        /** @var TroubleFiles $model */
        $model = TroubleFiles::findOne(['trouble_id' => $this->trouble->id, 'id' => $fileModel->id]);
        if ($model) {
            $model->delete();
        }
    }

    /**
     * @return TroubleFiles[]
     */
    protected function getFileModels()
    {
        return TroubleFiles::findAll(['trouble_id' => $this->trouble->id]);
    }
}