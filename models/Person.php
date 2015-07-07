<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name_nominative       ФИО в именительном падеже
 * @property string $name_genitive         ФИО в родительском падаже
 * @property string $post_nominative       Должность в именительном падеже
 * @property string $post_genitive         Должность в родительском падеже
 * @property string $signature_file_name    Название файла с подписью
 */
class Person extends ActiveRecord
{
    public $canDelete = true;

    public static function tableName()
    {
        return 'person';
    }

    public function getOldModeInfo()
    {
        return [
            'name'      => $this->name_nominative,
            'name_'     => $this->name_genitive,
            'position'  => $this->post_nominative,
            'position_' => $this->post_genitive,
            'sign'      => [
                'src' => str_replace('/images/', '', \Yii::$app->params['SIGNATURE_DIR']) . $this->signature_file_name,
            ]
        ];
    }

}