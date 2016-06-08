<?php

use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;

class m160607_100414_document_templates_pos_and_default extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(DocumentFolder::tableName(), 'default_for_business_id', $this->integer(11));
        $this->addColumn(DocumentTemplate::tableName(), 'sort', 'TINYINT(3) NOT NULL DEFAULT "0"');
    }

    public function down()
    {
        $this->dropColumn(DocumentFolder::tableName(), 'default_for_business_id');
        $this->dropColumn(DocumentTemplate::tableName(), 'sort');
    }
}