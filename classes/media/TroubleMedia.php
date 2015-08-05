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
        $model->user_id = Yii::$app->user->getId();

        $model->save();

        return $model;
    }

    protected function deleteFileModel(ActiveRecord $file)
    {
        /** @var TroubleFiles $model */
        $model = TroubleFiles::findOne(['trouble_id' => $this->trouble->id, 'id' => $file->id]);
        if ($model) {
            $model->delete();
        }
    }

    protected function getFileModels()
    {
        return TroubleFiles::findAll(['trouble_id' => $this->trouble->id]);
    }
}