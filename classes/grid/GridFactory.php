<?php
namespace app\classes\grid;

use app\classes\Assert;
use app\classes\grid\account\AccountGrid;
use app\classes\grid\account\TelecomMaintenance;
use app\classes\Singleton;
use Yii;

/**
 * @method static GridFactory me($args = null)
 * @property
 */
class GridFactory extends Singleton
{

    /**
     * @return AccountGrid[]
     */
    protected function getAccountGrids()
    {
        return [
            new TelecomMaintenance(),
        ];
    }

    /**
     * @return AccountGrid
     */
    public function getAccountGridByBusinessProcessId($businessProcessId)
    {
        foreach ($this->getAccountGrids() as $grid) {
            if ($grid->getBusinessProcessId() == $businessProcessId) {
                return $grid;
            }
        }

        Assert::isUnreachable('Business process grid not found');
    }
}