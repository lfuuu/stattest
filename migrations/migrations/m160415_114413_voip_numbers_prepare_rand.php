<?php

use app\models\Number;

class m160415_114413_voip_numbers_prepare_rand extends \app\classes\Migration
{
    public function up()
    {
        $tblName = Number::tableName();

        $this->addColumn($tblName, 'number_cut', $this->string(2));
        $this->createIndex('voip_numbers_number_cut', $tblName, 'number_cut');
        Number::updateAll([
            'number_cut' => new \yii\db\Expression('SUBSTRING(number, LENGTH(number)-1, 2)'),
        ]);

        $this->execute('
            CREATE TRIGGER add_voip_number BEFORE INSERT ON voip_numbers FOR EACH ROW BEGIN
                SET NEW.number_cut = SUBSTRING(NEW.number, LENGTH(NEW.number)-1, 2);
            END
        ');

        $this->execute('
            CREATE TRIGGER update_voip_number BEFORE UPDATE ON voip_numbers FOR EACH ROW BEGIN
                SET NEW.number_cut = SUBSTRING(NEW.number, LENGTH(NEW.number)-1, 2);
            END
        ');
    }

    public function down()
    {
        $tblName = Number::tableName();

        $this->dropIndex('voip_numbers_number_cut', $tblName);
        $this->dropColumn($tblName, 'number_cut');
        $this->execute('DROP TRIGGER IF EXISTS add_voip_number');
        $this->execute('DROP TRIGGER IF EXISTS update_voip_number');
    }
}