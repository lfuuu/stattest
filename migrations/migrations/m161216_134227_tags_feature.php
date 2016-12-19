<?php

use app\models\Tags;
use app\models\TagsResource;

class m161216_134227_tags_feature extends \app\classes\Migration
{
    public function up()
    {
        $tagsTableName = Tags::tableName();
        $tagsResourceTableName = TagsResource::tableName();

        $this->addColumn($tagsResourceTableName, 'feature', $this->string(50));

        $this->dropForeignKey('fk-' . $tagsResourceTableName . '-tag_id', $tagsResourceTableName);
        $this->dropIndex('tag_id-resource-resource_id', $tagsResourceTableName);

        $this->createIndex('tag_id-resource-resource_id-feature', $tagsResourceTableName, [
            'tag_id',
            'resource',
            'resource_id',
            'feature',
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
    }

    public function down()
    {
        $tagsTableName = Tags::tableName();
        $tagsResourceTableName = TagsResource::tableName();

        $this->dropForeignKey('fk-' . $tagsResourceTableName . '-tag_id', $tagsResourceTableName);
        $this->dropIndex('tag_id-resource-resource_id-feature', $tagsResourceTableName);
        $this->dropColumn($tagsResourceTableName, 'feature');

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
    }
}