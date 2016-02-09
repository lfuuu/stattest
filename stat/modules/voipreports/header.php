<?php
class m_voipreports_head extends IModuleHead
{
    public $module_name = 'voipreports';
    public $module_title = 'Межоператорка (Отчеты)';

    public $actions = array(
        'voip_7800_report' => array('voipreports','access'),
        'voip_local_report' => array('voipreports','access'),
        'voip_mgmn_report' => array('voipreports','access'),
        'by_dest_operator' => array('voipreports','access'),
        'by_source_operator' => array('voipreports','access'),
        'operators_traf' => array('voipreports', 'access'),
        'unrecognized' => array('voipreports', 'access'),

        'calc_volume' => array('voipreports', 'admin'),
        'cost_report' => array('voipreports', 'access'),

        'reconciliation_report' => array('voipreports', 'access'),

        'pricelist_report_list' => array('voipreports', 'access'),
        'pricelist_report_routing_list' => array('voipreports', 'access'),
        //'pricelist_report_operator_list' => array('voipreports', 'access'),
        'pricelist_report_analyze_list' => array('voipreports', 'access'),
        'pricelist_report_show' => array('voipreports', 'access'),
        'pricelist_report_edit' => array('voipreports', 'admin'),
        'pricelist_report_save' => array('voipreports', 'admin'),
        'pricelist_report_delete' => array('voipreports', 'admin'),
        'calls_report' => array('voipreports', 'access'),
    );

    public $menu = array(
        array('Анализ прайс-листов', 'pricelist_report_analyze_list'),
        //array('Сравнение операторов', 'pricelist_report_operator_list'),
        array('По маршрутизации', 'pricelist_report_routing_list'),
        array('Себестоимость', 'cost_report'),
        array('Отчет для сверок', 'reconciliation_report'),
        array('', 'voip_7800_report'),
        array('Voip 7800', 'voip_7800_report'),
        array('Voip Местные', 'voip_local_report'),
        array('Voip МГМН', 'voip_mgmn_report'),
        array('Отчет по звонкам', 'calls_report'),
        array('На кого ушли звонки', 'by_dest_operator'),
        array('От кого пришли звонки', 'by_source_operator'),
        array('Отчет по операторскому трафику voip', 'operators_traf'),
        array('Не распознанные вызовы', 'unrecognized'),
    );
}
