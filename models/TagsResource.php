<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use Yii;
use yii\db\Query;

/**
 * @property int $tag_id
 * @property string $resource
 * @property int $resource_id
 * @property string $feature
 *
 * @property-read Tags $tag
 * @property array $tagList
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
     * @return array
     */
    public function rules()
    {
        return [
            ['resource', 'string'],
            ['resource_id', 'integer'],
            ['feature', 'string'],
            ['tags', ArrayValidator::class],
            [['resource', 'resource_id',], 'required'],
            ['feature', 'default', 'value' => null],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tags::class, ['id' => 'tag_id']);
    }

    /**
     * @param string $resource
     * @param string $indexBy
     * @param int $resourceId
     * @param string|null $feature
     * @return array
     */
    public static function getTagList($resource, $indexBy = null, $resourceId = 0, $feature = null)
    {
        $query = (new Query)
            ->select(['t.id', 't.name'])
            ->from(['tc' => self::tableName()])
            ->innerJoin(['t' => Tags::tableName()], 't.id = tc.tag_id')
            ->andWhere(['tc.resource' => $resource])
            ->groupBy(['t.id', 't.name']);

        if ((int)$resourceId) {
            $query->andWhere(['tc.resource_id' => $resourceId]);
        }

        if (!empty($feature)) {
            $query->andWhere(['tc.feature' => $feature]);
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
        $this->tags = (array)$this->tags;

        $tagList = self::getTagList($this->resource, 'name', 0, $this->feature);
        $resourceTagList = self::getTagList($this->resource, 'name', $this->resource_id, $this->feature);
        $diffTags = array_diff(array_keys($resourceTagList), $this->tags);

        $transaction = self::getDb()->beginTransaction();

        try {
            $tagsToRemove = [];

            if (count($diffTags)) {
                foreach ($diffTags as $tagName) {
                    $tagsToRemove[] = $tagList[$tagName]['id'];
                }

                $conditions = [
                    'AND',
                    ['resource' => $this->resource],
                    ['resource_id' => $this->resource_id],
                    ['IN', 'tag_id', $tagsToRemove],
                ];

                if ($this->feature) {
                    $conditions[] = ['feature' => $this->feature];
                }

                self::deleteAll($conditions);
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
                            throw new ModelValidationException($tag);
                        }

                        $tagId = $tag->id;
                        $tagList[$tagName] = ['id' => $tag->id, 'name' => $tagName];
                    } else {
                        $tagId = $tagList[$tagName]['id'];
                    }

                    $tagCloudRecord = new self;
                    $tagCloudRecord->resource = $this->resource;
                    $tagCloudRecord->resource_id = $this->resource_id;
                    $tagCloudRecord->feature = $this->feature;
                    $tagCloudRecord->tag_id = $tagId;
                    if (!$tagCloudRecord->save()) {
                        throw new ModelValidationException($tagCloudRecord);
                    }
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
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