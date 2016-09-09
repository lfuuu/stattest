<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\classes\validators\ArrayValidator;
use yii\db\Query;

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
    public static function getTagsList($resource)
    {
        return
            (new Query)
                ->select(['t.id', 't.name'])
                ->from(['tc' => self::tableName()])
                ->innerJoin(['t' => Tags::tableName()], 't.id = tc.tag_id')
                ->where([
                    'tc.resource' => $resource,
                ])
                ->groupBy(['t.id'])
                ->all();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveAll()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            self::deleteAll([
                'resource' => $this->resource,
                'resource_id' => $this->resource_id,
            ]);

            foreach ($this->tags as $tagName) {
                $tag = Tags::findOne(['name' => $tagName]);

                if (is_null($tag)) {
                    $tag = new Tags;
                    $tag->name = $tagName;
                    $tag->save();
                }

                $tagCloudRecord = new self;
                $tagCloudRecord->resource = $this->resource;
                $tagCloudRecord->resource_id = $this->resource_id;
                $tagCloudRecord->tag_id = $tag->id;
                $tagCloudRecord->save();
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