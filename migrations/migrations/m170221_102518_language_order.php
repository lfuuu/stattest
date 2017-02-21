<?php
use app\models\Language;

/**
 * Class m170221_102518_language_order
 */
class m170221_102518_language_order extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Language::tableName(), 'order', $this->integer()->notNull()->defaultValue(0));

        $count = 0;

        foreach ([
                     Language::LANGUAGE_RUSSIAN,
                     Language::LANGUAGE_ENGLISH,
                     Language::LANGUAGE_MAGYAR,
                     Language::LANGUAGE_GERMANY,
                     Language::LANGUAGE_SLOVAK
                 ] as $lang) {
            $this->update(Language::tableName(), ['order' => $count++], ['code' => $lang]);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Language::tableName(), 'order');
    }
}
