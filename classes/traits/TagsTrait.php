<?php
namespace app\classes\traits;

use app\models\TagsResource;
use yii\helpers\ArrayHelper;

trait TagsTrait
{

    /**
     * @return array
     */
    public function getTagList()
    {
        return TagsResource::getTagList($this->formName(), 'id');
    }

    /**
     * @param string $feature
     * @return string[]
     */
    public function getTags($feature)
    {
        $tags = TagsResource::findAll([
            'resource' => $this->formName(),
            'resource_id' => $this->id,
            'feature' => $feature,
        ]);

        return ArrayHelper::getColumn($tags, function($row) { return $row->tag->name; });
    }

}