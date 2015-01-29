<?php
namespace app\models;

use app\dao\BillDocumentDao;
use yii\db\ActiveRecord;

/**
 * @property string $bill_no
 * @property string $ts
 * @property
 */
class BillDocument extends ActiveRecord
{
    public static function tableName()
    {
        return 'newbills_documents';
    }

    public static function dao()
    {
        return BillDocumentDao::me();
    }
}