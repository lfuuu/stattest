<?php

use app\models\document\DocumentFolder;
use app\models\document\DocumentTemplate;

class m160528_130638_document_folder_tree extends \app\classes\Migration
{
    public function up()
    {
        $documentFolderName = DocumentFolder::tableName();
        $documentTemplateName = DocumentTemplate::tableName();

        $this->addColumn($documentFolderName, 'parent_id', $this->integer(11)->notNull()->defaultValue(0));
        $this->dropColumn($documentFolderName, 'is_default');

        $this->alterColumn($documentTemplateName, 'folder_id', $this->integer(11)->defaultValue(null));
    }

    public function down()
    {
        $documentFolderName = DocumentFolder::tableName();
        $documentTemplateName = DocumentTemplate::tableName();

        $this->alterColumn($documentTemplateName, 'folder_id', 'TINYINT(3) UNSIGNED NULL DEFAULT NULL');

        $this->dropColumn($documentFolderName, 'parent_id');
        $this->addColumn($documentFolderName, 'is_default', 'TINYINT(1) NOT NULL DEFAULT "0"');
    }
}