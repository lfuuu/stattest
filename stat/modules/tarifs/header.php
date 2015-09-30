<?php

class m_tarifs_head extends IModuleHead{
    public $module_name = 'tarifs';
    public $module_title = 'Тарифы';

    var $actions=array(
            'default'             => array('tarifs','read'),
            'view'                => array('tarifs','read'),
            'edit'                => array('tarifs','read'),
            'delete'              => array('tarifs','edit'),
            'itpark'              => array('services_itpark','full'),
            'welltime'            => array('services_welltime','full'),
            'wellsystem'          => array('services_wellsystem','full'),
            'voip'                => array('tarifs','read'),
            'voip_edit'           => array('tarifs','edit'),
            'contracts'           => array('tarifs','edit'),
            'price_tel'           => array('tarifs','edit'),
            'virtpbx'             => array('tarifs','edit'),
            'sms'                 => array('tarifs','edit'),
        );

    public $menu;

    public function __construct()
    {
        $this->menu = array(
            array('Интернет',                'view','&m=internet'),
            array('Collocation',            'view','&m=collocation'),
            array('VPN',                    'view','&m=vpn'),
            //             array('Междугородняя связь',    'view','&m=russia'),
            //             array('Международная связь',    'view','&m=world'),
            array('Дополнительные услуги',    'view','&m=extra'),
            //array('IT Park',                'view','&m=itpark'),
            array('IT Park',                'itpark',''),
            array('Welltime',                'welltime',''),
            array('Виртуальная АТС',        'virtpbx',''),
            array('СМС',                   'sms',''),
            array('WellSystem',                'wellsystem',''),
            ['', 'contracts'],
            //            array('Старые доп.услуги',        'view','&m=add'),
            array('Договор-Прайс-Телефония',            'price_tel',''),
        );
    }
}
