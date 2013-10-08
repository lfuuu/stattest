<?php
class m_voipnew_head extends IModuleHead
{
    public $module_name = 'voipnew';
    public $module_title = 'IP Телефония (New)';

    public $rights = array(
        'voip' => array('Список операторов', 'access,admin', 'доступ,администрирование')
    );

    public $actions = array(
        'billing_settings' => array('voip', 'access'),
        'raw_files' => array('voip', 'access'),
        'view_raw_file' => array('voip', 'access'),
        'compare_raw_file' => array('voip', 'access'),
        'delete_raw_file' => array('voip', 'access'),
        'defs' => array('voip', 'access'),
        'activatedeactivate' => array('voip', 'access'),
        'upload' => array('voip', 'access'),
        'mtt_parse' => array('voip', 'access'),
        'get_mos_mob' => array('voip', 'access'),
        'pricelist' => array('voip', 'access'),
        'analyze_pricelist_report_list' => array('voip', 'access'),
        'analyze_pricelist_report_show' => array('voip', 'access'),
        'analyze_pricelist_report_edit' => array('voip', 'access'),
        'analyze_pricelist_report_delete' => array('voip', 'access'),
        'upload' => array('voip', 'access'),
        'pricelists' => array('voip', 'access'),
        'routing_report_list' => array('voip', 'access'),
        'routing_report_show' => array('voip', 'access'),
        'operator_report_list' => array('voip', 'access'),
        'operator_report_show' => array('voip', 'access'),
        'pricelist_report_list' => array('voip', 'access'),
        'pricelist_report_routing_list' => array('voip', 'access'),
        'pricelist_report_operator_list' => array('voip', 'access'),
        'pricelist_report_analyze_list' => array('voip', 'access'),
        'pricelist_report_show' => array('voip', 'access'),
        'pricelist_report_edit' => array('voip', 'access'),
        'pricelist_report_save' => array('voip', 'access'),
        'pricelist_report_delete' => array('voip', 'access'),
        'priority_list' => array('voip', 'access'),
        'set_lock_prefix' => array('voip', 'access'),
        'lock_by_price' => array('voip', 'access'),
        'calc_volume' => array('voip', 'access'),
        'calls_recalc' => array('voip', 'admin')
    );

    public $menu = array(
        array('Настройки', 'billing_settings'),
        array('Приоритеты', 'priority_list'),
        array('Цены', 'defs'),
        array('Прайс-листы', 'pricelists'),

        array('Отчет: Анализ прайс-листов', 'pricelist_report_analyze_list'),
        array('Отчет: Сравнение операторов', 'pricelist_report_operator_list'),
        array('Отчет: По маршрутизации', 'pricelist_report_routing_list'),

        array('Отчет: Анализ прайс-листов (Старый)', 'analyze_pricelist_report_list'),
        array('Отчет: Сравнение операторов (Старый)', 'operator_report_list'),
        array('Отчет: По маршрутизации (Старый)', 'routing_report_list'),
        array('Пересчет звонков', 'calls_recalc'),

    );
}
