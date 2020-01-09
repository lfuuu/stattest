<?php

namespace app\models\media;

use app\classes\media\BillExtMedia;
use app\classes\model\ActiveRecord;
use app\models\Bill;
use app\models\Trouble;

/**
 * @property int $id
 * @property string $bill_no
 * @property int $user_id
 * @property string $ts
 * @property string $comment
 * @property string $name
 * @property-read BillExtMedia $mediaManager
 */
class BillExtFiles extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbills_external_files';
    }

    /**
     * @return BillExtMedia
     */
    public function getMediaManager()
    {
        $bill = Bill::findOne(['bill_no' => $this->bill_no]);
        return new BillExtMedia($bill);
    }

}