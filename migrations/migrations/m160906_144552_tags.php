<?php

use app\models\Tags;
use app\models\TagsResource;

class m160906_144552_tags extends \app\classes\Migration
{
    public function up()
    {
        $tagsTableName = Tags::tableName();

        $this->createTable($tagsTableName, [
            'id' => $this->primaryKey(11),
            'name' => $this->string(255),
            'used_times' => $this->integer(6)->notNull()->defaultValue(0),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $tagsResourceTableName = TagsResource::tableName();

        $this->createTable($tagsResourceTableName, [
            'tag_id' => $this->integer(11),
            'resource' => $this->string(128),
            'resource_id' => $this->integer(11),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('tag_id-resource-resource_id', $tagsResourceTableName, [
            'tag_id',
            'resource',
            'resource_id',
        ], $isUnique = true);

        $this->addForeignKey(
            'fk-' . $tagsResourceTableName . '-tag_id',
            $tagsResourceTableName,
            'tag_id',
            $tagsTableName,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->execute('
            CREATE TRIGGER `remove_tags` BEFORE DELETE ON `tags_resource` FOR EACH ROW BEGIN
                UPDATE tags SET tags.used_times = tags.used_times - 1 WHERE tags.id = OLD.tag_id;
                DELETE FROM tags WHERE tags.used_times < 1;
            END
        ');
        $this->execute('
            CREATE TRIGGER `actual_tags` AFTER INSERT ON `tags_resource` FOR EACH ROW BEGIN
                UPDATE tags SET tags.used_times = tags.used_times + 1 WHERE tags.id = NEW.tag_id;
            END
        ');
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS remove_tags');
        $this->execute('DROP TRIGGER IF EXISTS actual_tags');
        $this->dropTable(TagsResource::tableName());
        $this->dropTable(Tags::tableName());
    }
}