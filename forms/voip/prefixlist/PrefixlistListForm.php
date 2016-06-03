<?php
namespace app\forms\voip\prefixlist;

use app\models\voip\Prefixlist;
use yii\db\Query;

class PrefixlistListForm extends PrefixlistForm
{

    public function rules()
    {
        return [
            [['name', ], 'string'],
            [['type_id',], 'integer'],
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return Prefixlist::find()->orderBy('name');
    }

}