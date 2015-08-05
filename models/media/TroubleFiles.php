<?php

namespace app\models\media;

use app\models\Trouble;
use yii\db\ActiveRecord;
use app\classes\media\TroubleMedia;

class TroubleFiles extends ActiveRecord
{

    public static function tableName()
    {
        return 'tt_files';
    }

    public function getMediaManager()
    {
        $trouble = Trouble::findOne(['id' => $this->trouble_id]);
        return new TroubleMedia($trouble);
    }

}