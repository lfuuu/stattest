<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name_nominativus       ФИО в именительном падеже
 * @property string $name_genitivus         ФИО в родительском падаже
 * @property string $post_nominativus       Должность в именительном падеже
 * @property string $post_genitivus         Должность в родительском падеже
 * @property string $signature_file_name    Название файла с подписью
 */
class Person extends ActiveRecord
{
    public $canDelete = true;

    public static function tableName()
    {
        return 'person';
    }
}