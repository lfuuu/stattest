<?php
class m_voipreports_head extends IModuleHead
{
    public $module_name = 'voipreports';
    public $module_title = 'IP Телефония (Отчеты)';

    public $rights = array(
        'voipreports' => array('Отчеты', 'access,admin', 'доступ,администрирование')
    );

    public $actions = array(
        'voip_7800_report' => array('voipreports','access'),
        'voip_local_report' => array('voipreports','access'),
        'voip_mgmn_report' => array('voipreports','access'),
    );

    public $menu = array(
        array('Voip 7800', 'voip_7800_report'),
        array('Voip Местные', 'voip_local_report'),
        array('Voip МГМН', 'voip_mgmn_report'),
    );
}
