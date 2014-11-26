<?php
namespace app\queries;

use app\models\Module;
use yii\db\ActiveQuery;

/**
 * @method Module[] all($db = null)
 * @property
 */
class ModuleQuery extends ActiveQuery
{
    public function installed()
    {

        return $this;
    }

    public function orderByLoadOrder()
    {
        ;
        return $this;
    }
}