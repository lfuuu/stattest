<?php
namespace app\classes\traits;

use Yii;

/**
 * Перевод названий полей модели
 * Сами переводы хранятся в messages/models/модель
 *
 * @method static string tableName()
 * @method string[] attributes()
 */
trait AttributeLabelsTraits
{
    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        $tableName = self::tableName();

        $attributeLabels = [];
        foreach ($this->attributes() as $attribute) {
            $attributeLabels[$attribute] = Yii::t('models/' . $tableName, $attribute);
        }
        return $attributeLabels;
    }

}