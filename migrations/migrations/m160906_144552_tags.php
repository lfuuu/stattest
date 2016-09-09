<?php

use app\models\Tags;
use app\models\TagsResource;

class m160906_144552_tags extends \app\classes\Migration
{
    public function up()
    {
        $tagsTableName = Tags::tableName();

        $this->createTable($tagsTableName, [
            'id' => $this->integer(11),
            'name' => $this->string(255),
            'used_times' => $this->integer(6)->notNull()->defaultValue(0),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addPrimaryKey('id', $tagsTableName, 'id');
        $this->execute('ALTER TABLE ' . $tagsTableName . ' MODIFY id int(11) NOT NULL AUTO_INCREMENT');

        $cloudTableName = TagsResource::tableName();

        $this->createTable($cloudTableName, [
            'tag_id' => $this->integer(11),
            'resource' => $this->string(128),
            'resource_id' => $this->integer(11),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('tag_id-resource-resource_id', $cloudTableName, [
            'tag_id',
            'resource',
            'resource_id',
        ], $isUnique = true);

        $this->addForeignKey(
            'fk-' . $cloudTableName . '-tag_id',
            $cloudTableName,
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