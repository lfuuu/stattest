<?php

use app\models\DidGroup;

/**
 * Class m210330_170028_change_did_group_table
 */
class m210330_170028_change_did_group_table extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {

        $table = Yii::$app->db->schema->getTableSchema('did_group');
        for($i = 1; $i <= 24; $i++){
            if(isset($table->columns['price' . $i])){
                $this->dropColumn(DidGroup::tableName(), 'price' . $i);
            }

            if(isset($table->columns['tariff_status_package' . $i])){
                if ($i < 19) {
                    $this->dropForeignKey('fk-tariff_status_package' . $i, DidGroup::tableName());
                }
                $this->dropColumn(DidGroup::tableName(), 'tariff_status_package' . $i);
            }

            if(isset($table->columns['tariff_status_main' . $i])){
                if ($i < 19) {
                    $this->dropForeignKey('fk-tariff_status_main' . $i, DidGroup::tableName());
                }
                $this->dropColumn(DidGroup::tableName(), 'tariff_status_main' . $i);
            }

        }

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $table = Yii::$app->db->schema->getTableSchema('did_group');
        for($i = 1; $i <= 24; $i++){

            if(!isset($table->columns['price' . $i])){
                $this->addColumn(DidGroup::tableName(), 'price' . $i, $this->integer());
            }

            if(!isset($table->columns['tariff_status_package' . $i])){
                $this->addColumn(DidGroup::tableName(), 'tariff_status_package' . $i, $this->integer());
                if ($i < 19){
                    $this->addForeignKey(
                        'fk-tariff_status_package'.$i,
                        'did_group',
                        'tariff_status_package'.$i,
                        'uu_tariff_status',
                        'id',
                    );
                }

            }

            if(!isset($table->columns['tariff_status_main' . $i])){

                $this->addColumn(DidGroup::tableName(), 'tariff_status_main' . $i, $this->integer());
                if ($i < 19){
                    $this->addForeignKey(
                        'fk-tariff_status_main'.$i,
                        'did_group',
                        'tariff_status_main'.$i,
                        'uu_tariff_status',
                        'id',
                    );
                }

            }




        }
    }
} 
