<?php

namespace app\models;

use Yii;
use LogicException;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\classes\validators\ArrayValidator;

/**
 * @property int $tag_id
 * @property string $resource
 * @property int $resource_id
 *
 * @property Tags $tag
 * @property [] $tagList
 */
class TagsResource extends ActiveRecord
{

    public $tags = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tags_resource';
    }

    /**
     * @return []
     */
    public function rules()
    {
        return [
            ['resource', 'string'],
            ['resource_id', 'integer'],
            ['tags', ArrayValidator::className()],
            [['resource', 'resource_id', ], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tags::className(), ['id' => 'tag_id']);
    }

    /**
     * @param string $resource
     * @param string $indexBy
     * @param int $resourceId
     * @return []
     */
    public static function getTagList($resource, $indexBy = null, $resourceId = 0)
    {
        $query =
            (new Query)
                ->select(['t.id', 't.name'])
                ->from(['tc' => self::tableName()])
                ->innerJoin(['t' => Tags::tableName()], 't.id = tc.tag_id')
                ->andWhere(['tc.resource' => $resource])
                ->groupBy(['t.id', 't.name']);

        if ((int)$resourceId) {
            $query->andWhere(['resource_id' => $resourceId]);
        }
        if (!is_null($indexBy)) {
            $query->indexBy($indexBy);
        }

        return $query->all();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveAll()
    {
        $this->tags = (array) $this->tags;

        $tagList = self::getTagList($this->resource, 'name');
        $resourceTagList = self::getTagList($this->resource, 'name', $this->resource_id);
        $diffTags = array_diff(array_keys($resourceTagList), $this->tags);

        $transaction = self::getDb()->beginTransaction();

        try {
            $tagsToRemove = [];

            if (count($diffTags)) {
                foreach ($diffTags as $tagName) {
                    $tagsToRemove[] = $tagList[$tagName]['id'];
                }
                self::deleteAll([
                    'and',
                    ['resource' => $this->resource],
                    ['resource_id' => $this->resource_id],
                    ['IN', 'tag_id', $tagsToRemove],
                ]);
            }

            foreach ($this->tags as $tagName) {
                if (empty($tagName)) {
                    continue;
                }

                if (!array_key_exists($tagName, $resourceTagList)) {
                    if (!array_key_exists($tagName, $tagList)) {
                        $tag = new Tags;
                        $tag->name = $tagName;
                        if (!$tag->save()) {
                            throw new LogicException(implode(' ', $tag->getFirstErrors()));
                        }
                        $tagId = $tag->id;
                        $tagList[$tagName] = ['id' => $tag->id, 'name' => $tagName];
                    } else {
                        $tagId = $tagList[$tagName]['id'];
                    }

                    $tagCloudRecord = new self;
                    $tagCloudRecord->resource = $this->resource;
                    $tagCloudRecord->resource_id = $this->resource_id;
                    $tagCloudRecord->tag_id = $tagId;
                    if (!$tagCloudRecord->save()) {
                        throw new LogicException(implode(' ', $tagCloudRecord->getFirstErrors()));
                    }
                }
            }

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->tag->name;
    }

}