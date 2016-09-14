<?php
namespace app\classes\traits;

use app\models\TagsResource;
use yii\helpers\ArrayHelper;

trait TagsTrait
{

    /**
     * @return []
     */
    public function getTagList()
    {
        return TagsResource::getTagList($this->formName(), 'id');
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        $tags = TagsResource::findAll([
            'resource' => $this->formName(),
            'resource_id' => $this->id,
        ]);

        return ArrayHelper::getColumn($tags, function($row) { return $row->tag->name; });
    }

}