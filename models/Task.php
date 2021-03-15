<?php

namespace app\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\traits\GridSortTrait;
use app\dao\CityDao;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $created_at
 * @property string $filter_class
 * @property string $filter_data_json
 * @property string $params_json
 * @property string $status
 * @property string $progress
 * @property integer $count_all
 * @property integer $count_done
 * @property integer $user_id
 */
class Task extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'task';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('UTC_TIMESTAMP()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['status'], 'string'],
            ['status', 'default', 'value' => 'plan'],
        ];
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    public function log($msg)
    {
        $this->progress = $this->progress . PHP_EOL . '<br>' . $msg;
        $this->save();
    }

    public function setCountAll($cnt = 0)
    {
        $this->count_all = $cnt;
        $this->save();
    }

    public function setCount($cnt = 0)
    {
        $this->count_done = $cnt;
        $this->save();
    }
}