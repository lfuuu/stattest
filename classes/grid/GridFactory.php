<?php
namespace app\classes\grid;

use app\classes\Assert;
use app\classes\grid\account\AccountGrid;
use app\classes\grid\account\InternalOffice;
use app\classes\grid\account\InternetShopMaintenance;
use app\classes\grid\account\OperatorClients;
use app\classes\grid\account\OperatorFormal;
use app\classes\grid\account\OperatorInfrastructure;
use app\classes\grid\account\OperatorOperators;
use app\classes\grid\account\Partner;
use app\classes\grid\account\PartnerMaintenance;
use app\classes\grid\account\ProviderMaintenance;
use app\classes\grid\account\ProviderOrders;
use app\classes\grid\account\TelecomMaintenance;
use app\classes\grid\account\TelecomReports;
use app\classes\grid\account\TelecomSales;
use app\classes\grid\account\WelltimeMaintenance;
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
            new TelecomReports(),
            new TelecomSales(),
            new InternetShopMaintenance(),
            new InternalOffice(),
            new OperatorOperators(),
            new OperatorClients(),
            new OperatorFormal(),
            new OperatorInfrastructure(),
            new ProviderMaintenance(),
            new ProviderOrders(),
            new PartnerMaintenance(),
            new WelltimeMaintenance(),
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