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
        $this->andWhere('is_installed = 1');
        return $this;
    }

    public function orderByLoadOrder()
    {
        $this->orderBy('load_order');
        return $this;
    }
}