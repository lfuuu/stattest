<?php
class m_voipnew_head extends IModuleHead
{
    public $module_name = 'voipnew';
    public $module_title = 'IP Телефония';

    public $rights = array(
        'voip' => array('Список операторов', 'access,admin,catalog', 'доступ,администрирование,справочники')
    );

    public $actions = array(
        'catalogs' => array('voip', 'catalog'),
        'catalog_prefix' => array('voip', 'catalog'),
        'raw_files' => array('voip', 'access'),
        'view_raw_file' => array('voip', 'access'),
        'compare_raw_file' => array('voip', 'access'),
        'delete_raw_file' => array('voip', 'access'),
        'defs' => array('voip', 'access'),
        'activatedeactivate' => array('voip', 'access'),
        'change_raw_file_start_date' => array('voip', 'access'),
        'upload' => array('voip', 'access'),
        'mtt_parse' => array('voip', 'access'),
        'get_mos_mob' => array('voip', 'access'),
        'pricelist' => array('voip', 'access'),
        'upload' => array('voip', 'access'),
        'operators' => array('voip', 'access'),
        'client_pricelists' => array('voip', 'access'),
        'operator_pricelists' => array('voip', 'access'),
        'operator_networks' => array('voip', 'access'),
        'network_prices' => array('voip', 'access'),
        'pricelists' => array('voip', 'access'),
        'pricelist_report_list' => array('voip', 'access'),
        'pricelist_report_routing_list' => array('voip', 'access'),
        'pricelist_report_operator_list' => array('voip', 'access'),
        'pricelist_report_analyze_list' => array('voip', 'access'),
        'pricelist_report_show' => array('voip', 'access'),
        'pricelist_report_edit' => array('voip', 'access'),
        'pricelist_report_save' => array('voip', 'access'),
        'pricelist_report_delete' => array('voip', 'access'),
        'cost_report' => array('voip', 'access'),
        'priority_list' => array('voip', 'access'),
        'set_lock_prefix' => array('voip', 'access'),
        'lock_by_price' => array('voip', 'access'),
        'calc_volume' => array('voip', 'access'),
        'calls_recalc' => array('voip', 'admin'),
        'network_list' => array('voip', 'access'),
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
        array('Местные цены', 'network_prices'),
        array('', 'catalogs'),
        array('Cети', 'network_list'),
        array('', 'catalogs'),
        array('Отчет: Анализ прайс-листов', 'pricelist_report_analyze_list'),
        array('Отчет: Сравнение операторов', 'pricelist_report_operator_list'),
        array('Отчет: По маршрутизации', 'pricelist_report_routing_list'),
        array('Отчет: Себестоимость', 'cost_report'),
        array('', 'catalogs'),
        array('Пересчет звонков', 'calls_recalc'),
        array('Приоритеты', 'priority_list'),
    );
}
