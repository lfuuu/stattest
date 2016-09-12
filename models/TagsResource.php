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
     * @return []
     */
    public static function getTagList($resource)
    {
        return
            (new Query)
                ->select(['t.id', 't.name'])
                ->from(['tc' => self::tableName()])
                ->innerJoin(['t' => Tags::tableName()], 't.id = tc.tag_id')
                ->where([
                    'tc.resource' => $resource,
                ])
                ->groupBy(['t.id', 't.name'])
                ->all();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveAll()
    {
        $tagList = ArrayHelper::map(self::getTagList($this->resource), 'name', 'id');

        self::deleteAll([
            'resource' => $this->resource,
            'resource_id' => $this->resource_id,
        ]);

        $transaction = self::getDb()->beginTransaction();
        try {
            foreach ($this->tags as $tagName) {
                if (empty($tagName)) {
                    continue;
                }

                $tagId = 0;
                if (!array_key_exists($tagName, $tagList)) {
                    $tag = new Tags;
                    $tag->name = $tagName;
                    if ($tag->save()) {
                        $tagId = $tag->id;
                    } else {
                        throw new LogicException(implode(' ', $tag->getFirstErrors()));
                    }
                } else {
                    $tagId = $tagList[$tagName];
                }

                if ((int)$tagId) {
                    $tagCloudRecord = new self;
                    $tagCloudRecord->resource = $this->resource;
                    $tagCloudRecord->resource_id = $this->resource_id;
                    $tagCloudRecord->tag_id = $tagId;
                    $tagCloudRecord->save();
                }
            }

            $transaction->commit();
        }
        catch (LogicException $e) {
            $transaction->rollBack();
            Yii::error($e);
            return false;
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