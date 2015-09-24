<?php
namespace app\models\document;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class DocumentFolder extends ActiveRecord
{

    public static function tableName()
    {
        return 'document_folder';
    }

    public static function getList()
    {
        return ArrayHelper::map(self::find()->orderBy("sort")->all(), 'id', 'name');
    }
}
