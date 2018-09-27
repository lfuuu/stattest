<?php
namespace app\classes\traits;

use Yii;
use ReflectionClass;
use app\models\TagsResource;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

trait TagsTrait
{

    public $tags_filter = []; // Входящий параметр, aka Database field name

    /**
     * @return array
     */
    public function getTagList()
    {
        return ArrayHelper::getColumn(TagsResource::getTagList($this->formName(), 'id'), 'name');
    }

    /**
     * @param string|null $feature
     * @return string[]
     */
    public function getTags($feature = null)
    {
        $conditions = [
            'resource' => $this->formName(),
            'resource_id' => $this->id,
        ];

        if (!is_null($feature)) {
            $conditions['feature'] = $feature;
        }

        $tags = TagsResource::findAll($conditions);

        return ArrayHelper::getColumn($tags, function ($row) {
            return $row->tag->name;
        });
    }

    /**
     * Resource class must be instance of ActiveRecord or null
     * Using resource class example: $this->setTagsFilter($query, ImportantEventsNames::class)
     *
     * @param ActiveQuery $query
     * @param null|ActiveRecord $resourceClassName
     */
    public function setTagsFilter(ActiveQuery $query, $resourceClassName = null)
    {
        $tableName = self::tableName();
        $className = self::class;

        if (!is_null($resourceClassName)) {
            $tableName = $resourceClassName::tableName();
            $className = get_class($resourceClassName);
        }

        if (is_array($this->tags_filter) && count($this->tags_filter)) {
            $query->innerJoin(
                ['tags' => TagsResource::tableName()],
                '
                    tags.resource = :resource
                    AND tags.resource_id = ' . $tableName . '.id
                ',
                [
                    'resource' => (new ReflectionClass($className))->getShortName(),
                ]
            );
            $query->andWhere(['IN', 'tags.tag_id', $this->tags_filter]);
        }
    }

    /**
     * @return bool
     */
    private function _applyTags()
    {
        $tags = new TagsResource;
        if ($tags->load(Yii::$app->request->post(), '')) {
            $tags->resource = $this->formName();
            $tags->resource_id = $this->id;
            $tags->feature = null;
            if ($tags->validate()) {
                return $tags->saveAll();
            }
        }

        return false;
    }

}