<?php

namespace app\models\media;

use app\classes\media\TroubleMedia;
use app\classes\model\ActiveRecord;
use app\models\Trouble;

/**
 * @property TroubleMedia $mediaManager
 */
class TroubleFiles extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_files';
    }

    /**
     * @return TroubleMedia
     */
    public function getMediaManager()
    {
        $trouble = Trouble::findOne(['id' => $this->trouble_id]);
        return new TroubleMedia($trouble);
    }

}