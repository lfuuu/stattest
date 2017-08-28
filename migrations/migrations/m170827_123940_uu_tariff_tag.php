<?php
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffTag;

/**
 * Class m170827_123940_uu_tariff_tag
 */
class m170827_123940_uu_tariff_tag extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tariffTagTableName = TariffTag::tableName();
        $this->createTable($tariffTagTableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->insert($tariffTagTableName, ['id' => TariffTag::ID_HIT, 'name' => 'Хит продаж']);

        $tariffTableName = Tariff::tableName();
        $this->addColumn($tariffTableName, 'tag_id', $this->integer());
        $this->addForeignKey('tag_id', $tariffTableName, 'tag_id', $tariffTagTableName, 'id', 'RESTRICT', 'RESTRICT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tariffTableName = Tariff::tableName();
        $this->dropForeignKey('tag_id', $tariffTableName);
        $this->dropColumn($tariffTableName, 'tag_id');

        $tariffTagTableName = TariffTag::tableName();
        $this->dropTable($tariffTagTableName);
    }
}
