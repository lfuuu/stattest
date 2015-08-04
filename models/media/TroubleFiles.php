<?php

namespace app\models\media;

use yii\db\ActiveRecord;
use app\classes\media\TroubleMedia as MediaManager;

class TroubleFiles extends ActiveRecord
{

    public static function tableName()
    {
        return 'tt_files';
    }

    public function getMediaManager()
    {
        return new MediaManager($this->id);
    }

}