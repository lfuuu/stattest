<?php

include_once 'voip_7800_report.php';
include_once 'voip_local_report.php';
include_once 'voip_mgmn_report.php';
include_once 'by_dest_operator_report.php';
include_once 'by_source_operator_report.php';
include_once 'analyze_pricelist_report.php';
include_once 'operator_report.php';
include_once 'routing_report.php';
include_once 'pricelist_report.php';
include_once 'cost_report.php';
include_once 'operators_traf_report.php';
include_once 'unrecognized_report.php';
include_once 'reconciliation_report.php';


class m_voipreports extends IModule
{
    private $_inheritances = array();

    public function __construct()
    {
        $this->_addInheritance(new m_voipreports_voip_7800_report);
        $this->_addInheritance(new m_voipreports_voip_local_report);
        $this->_addInheritance(new m_voipreports_voip_mgmn_report);
        $this->_addInheritance(new m_voipreports_by_dest_operator_report);
        $this->_addInheritance(new m_voipreports_by_source_operator_report);
        $this->_addInheritance(new m_voipreports_operators_traf);
        $this->_addInheritance(new m_voipreports_unrecognized_report);

        $this->_addInheritance(new m_voipreports_analyze_pricelist_report);
        $this->_addInheritance(new m_voipreports_operator_report);
        $this->_addInheritance(new m_voipreports_routing_report);
        $this->_addInheritance(new m_voipreports_pricelist_report);
        $this->_addInheritance(new m_voipreports_cost_report);
        $this->_addInheritance(new m_voipreports_reconciliation_report);
    }

    public function __call($method, array $arguments = array())
    {
        foreach ($this->_inheritances as $inheritance) {
            $inheritance->invoke($method, $arguments);
        }
    }

    protected function _addInheritance(Inheritance $inheritance)
    {
        $this->_inheritances[get_class($inheritance)] = $inheritance;
        $inheritance->module = $this;
    }

}
