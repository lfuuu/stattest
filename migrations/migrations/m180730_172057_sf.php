<?php

 class m180730_172057_sf extends \app\classes\Migration
 {
     /**
      * Up
      */
     public function safeUp()
     {
         $invoiceTable = \app\models\Invoice::tableName();

         $this->createTable($invoiceTable, [
             'id' => $this->primaryKey(),
             'number' => $this->string(32),
             'organization_id' => $this->integer()->notNull(),
             'idx' => $this->integer()->notNull()->defaultValue(1),
             'type_id' => $this->integer()->notNull()->defaultValue(\app\models\Invoice::TYPE_1),
             'bill_no' => 'varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL',
             'date' => $this->date()->notNull(),
             'sum' => $this->decimal(12,4)->notNull()->defaultValue(0)
         ]);

         $this->createIndex('idx-number', $invoiceTable, 'number');

         $billTable = \app\models\Bill::tableName();

         $this->addForeignKey('fk-'.$invoiceTable.'-bill_no-'.$billTable.'-bill_no', $invoiceTable, 'bill_no', $billTable, 'bill_no', 'CASCADE', 'CASCADE');
     }

     /**
      * Down
      */
     public function safeDown()
     {
         $this->dropTable(\app\models\Invoice::tableName());
     }
 }
