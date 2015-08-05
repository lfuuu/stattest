<?php

namespace app\classes\media;

use app\models\Trouble;
use DateTime;
use Yii;
use app\models\media\TroubleFiles;

class TroubleMedia extends MediaManager
{
    /** @var Trouble */
    private $trouble;

    public function __construct(Trouble $trouble)
    {
        $this->trouble = $trouble;
    }

    public function getFolder()
    {
        return 'files/troubles';
    }

    /**
     * @return TroubleFiles
     */
    protected function createFileModel($name, $comment)
    {
        $model = new TroubleFiles();
        $model->trouble_id = $this->trouble->id;
        $model->ts = (new DateTime())->format(DateTime::ATOM);

        $model->name = $name;
        $model->comment = $comment;
        $model->user_id = Yii::$app->user->getId();

        $model->save();

        return $model;
    }

    protected function deleteFileModel($fileId)
    {
        /** @var TroubleFiles $model */
        $model = TroubleFiles::findOne(['trouble_id' => $this->trouble->id, 'id' => $fileId]);
        if ($model) {
            $model->delete();
        }
    }

    protected function getFileModels()
    {
        return TroubleFiles::findAll(['trouble_id' => $this->trouble->id]);
    }
}