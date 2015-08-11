<?php
namespace app\forms\voip\destination;

use app\models\voip\Destination;
use yii\db\Query;

class DestinationListForm extends DestinationForm
{

    public function rules()
    {
        return [
            [['name',], 'string']
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return Destination::find()->orderBy('name');
    }

}