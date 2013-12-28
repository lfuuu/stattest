<?php
class m_voipnew_head extends IModuleHead
{
    public $module_name = 'voipnew';
    public $module_title = 'IP Телефония';

    public $rights = array(
        'voip' => array('Список операторов', 'access,admin', 'доступ,администрирование')
    );

    public $actions = array(
        'catalogs' => array('voip', 'access'),
        'catalog_prefix' => array('voip', 'access'),
        'raw_files' => array('voip', 'access'),
        'view_raw_file' => array('voip', 'access'),
        'compare_raw_file' => array('voip', 'access'),
        'delete_raw_file' => array('voip', 'admin'),
        'defs' => array('voip', 'access'),
        'activatedeactivate' => array('voip', 'admin'),
        'change_raw_file_start_date' => array('voip', 'admin'),
        'upload' => array('voip', 'admin'),
        'pricelist' => array('voip', 'access'),
        'operators' => array('voip', 'access'),
        'client_pricelists' => array('voip', 'access'),
        'operator_pricelists' => array('voip', 'access'),
        'operator_networks' => array('voip', 'access'),
        'pricelists' => array('voip', 'access'),
        'priority_list' => array('voip', 'access'),
        'set_lock_prefix' => array('voip', 'admin'),
        'lock_by_price' => array('voip', 'admin'),
        'calls_recalc' => array('voip', 'admin'),
        'network_list' => array('voip', 'access'),
        'network_prices' => array('voip', 'access'),
        'network_price' => array('voip', 'access'),
        'network_config_show' => array('voip', 'admin'),
        'network_file_upload' => array('voip', 'admin'),
        'network_file_show' => array('voip', 'access'),
    );

    public $menu = array(
        array('Справочники', 'catalogs'),
        array('Операторы', 'operators'),
        array('Клиенстские прайслисты', 'client_pricelists'),
        array('Операторские прайслисты', 'operator_pricelists'),
        array('Операторские сети', 'operator_networks'),
        array('', 'catalogs'),
        array('Cети', 'network_list'),
        array('Местные цены', 'network_prices'),
        array('', 'catalogs'),
        array('Пересчет звонков', 'calls_recalc'),
        array('Приоритеты', 'priority_list'),
    );
}
