<?php
return [
    'ats' => [
        'name' => 'Управление ATC',
        'permissions' => [
            'access'    =>  'доступ',
            'support'   =>  'ограниченный доступ',
        ],
    ],
    'ats2' => [
        'name' => 'Учетные записи SIP',
        'permissions' => [
            'access'    =>  'доступ',
        ],
    ],
    'clients' => [
        'name' => 'Работа с клиентами',
        'permissions' => [
            'read'          =>  'просмотр вообще',
            'read_filter'   =>  'просмотр с фильтрами',
            'read_all'      =>  'просмотр всех',
            'new'           =>  'создание',
            'edit'          =>  'редактирование',
            'restatus'      =>  'изменение статуса',
            'edit_tele'     =>  'редактирование для телемаркетинга',
            'sale_channels' =>  'редактирование каналов продаж',
            'file'          =>  'доступ к файлам',
            'inn_double'    =>  'заведение совпадающих ИНН',
            'all4net'       =>  'синхронизация с all4net',
            'history_edit'  =>  'редактирование истории',
            'client_type_change' => 'Изменение тип договора'
        ],
    ],
    'data' => [
        'name' => 'Данные справочников',
        'permissions' => [
            'access'  =>  'доступ',
        ],
    ],
    'employeers' => [
        'name' => 'Сотрудники',
        'permissions' => [
            'r'       =>  'чтение',
        ],
    ],
    'incomegoods' => [
        'name' => 'Закупки',
        'permissions' => [
            'access'  =>  'доступ',
            'admin'   =>  'администрирование',
        ],
    ],
    'logs' => [
        'name' => 'Логи',
        'permissions' => [
            'read'    =>  'просмотр',
        ],
    ],
    'mail' => [
        'name' => 'Письма клиентам',
        'permissions' => [
            'r'       =>  'просмотр PM',
            'w'       =>  'работа с рассылкой',
        ],
    ],
    'monitoring' => [
        'name' => 'Просмотр данных мониторинга',
        'permissions' => [
            'view'    =>  'просмотр',
            'top'     =>  'панелька сверху',
            'edit'    =>  'редактирование списка VIP-клиентов',
            'graphs'  =>  'просмотр графиков динамики',
        ],
    ],
    'newaccounts_bills' => [
        'name' => 'Счета',
        'permissions' => [
            'read'      =>  'просмотр',
            'edit'      =>  'изменение',
            'delete'    =>  'удаление',
            'admin'     =>  'изменение счета в любое время',
            'del_docs'  =>  'Удаление отсканированных актов',
            'edit_ext'  =>  'Редактирование номера внешнего счета',
        ],
    ],
    'newaccounts_payments' => [
        'name' => 'Платежи',
        'permissions' => [
            'read'    =>  'просмотр',
            'edit'    =>  'изменение',
            'delete'  =>  'удаление',
        ],
    ],
    'newaccounts_mass' => [
        'name' => 'Массовые операции',
        'permissions' => [
            'access'  =>  'доступ',
        ],
    ],
    'newaccounts_balance' => [
        'name' => 'Баланс',
        'permissions' => [
            'read'    =>  'просмотр',
        ],
    ],
    'newaccounts_usd' => [
        'name' => 'Курс доллара',
        'permissions' => [
            'access'  =>  'доступ',
        ],
    ],
    'routers_routers' => [
        'id'   => 'routers_routers',
        'name' => 'Роутеры',
        'permissions' => [
            'r'       =>  'чтение',
            'edit'    =>  'редактирование',
            'add'     =>  'добавление',
            'delete'  =>  'удаление',
        ],
    ],
    'routers_devices' => [
        'name' => 'Клиентские устройства',
        'permissions' => [
            'r'       =>  'чтение',
            'edit'    =>  'редактирование',
            'add'     =>  'добавление',
            'delete'  =>  'удаление',
        ],
    ],
    'routers_models' => [
        'name' => 'Модели клиентских устройств',
        'permissions' => [
            'r'       =>  'чтение',
            'w'       =>  'редактирование',
        ],
    ],
    'routers_nets' => [
        'name' => 'Сети',
        'permissions' => [
            'r'       =>  'доступ',
        ],
    ],
    'send' => [
        'name' => 'Массовая отправка счетов',
        'permissions' => [
            'r'       =>  'просмотр состояния',
            'send'    =>  'отправка',
        ],
    ],
    'services_internet' => [
        'name' => 'Интернет',
        'permissions' => [
            'r'         =>  'просмотр',
            'edit'      =>  'изменение',
            'addnew'    =>  'добавление',
            'activate'  =>  'активирование',
            'close'     =>  'отключение',
            'full'      =>  'полная информация по сетям (общее с collocation)',
            'edit_off'  =>  'редактирование отключенных сетей (общее с collocation)',
            'tarif'     =>  'изменение тарифа (общее с collocation)',
        ],
    ],
    'services_collocation' => [
        'name' => 'Collocation',
        'permissions' => [
            'r'         =>  'просмотр',
            'edit'      =>  'редактирование',
            'addnew'    =>  'добавление',
            'activate'  =>  'активирование',
            'close'     =>  'отключение',
        ],
    ],
    'services_voip' => [
        'name' => 'IP Телефония',
        'permissions' => [
            'r'             =>  'просмотр',
            'edit'          =>  'редактирование',
            'addnew'        =>  'добавление',
            'full'          =>  'доступ ко всем полям',
            'activate'      =>  'активирование',
            'close'         =>  'отключение',
            'view_reg'      =>  'просмотр регистрации',
            'view_regpass'  =>  'отображение пароля',
            'send_settings' =>  'выслать настройки',
            'e164'          =>  'номерные емкости',
            'del4000'       =>  'удалять невключенные номера',
        ],
    ],
    'services_domains' => [
        'name' => 'Доменные имена',
        'permissions' => [
            'r'       =>  'просмотр',
            'edit'    =>  'редактирование',
            'addnew'  =>  'добавление',
            'close'   =>  'отключение',
        ],
    ],
    'services_mail' => [
        'name' => 'E-mail',
        'permissions' => [
            'r'         =>  'просмотр',
            'edit'      =>  'редактирование',
            'addnew'    =>  'добавление',
            'full'      =>  'доступ ко всем полям',
            'activate'  =>  'активирование',
            'chpass'    =>  'смена пароля',
            'whitelist' =>  'белый список',
        ],
    ],
    'services_ppp' => [
        'name' => 'PPP-логины',
        'permissions' => [
            'r'         =>  'просмотр',
            'edit'      =>  'редактирование',
            'addnew'    =>  'добавление',
            'full'      =>  'доступ ко всем полям',
            'activate'  =>  'активирование',
            'chpass'    =>  'смена пароля',
            'close'     =>  'отключение',
        ],
    ],
    'services_additional' => [
        'name' => 'Дополнительные услуги',
        'permissions' => [
            'r'         =>  'просмотр',
            'r_old'     =>  'просмотр старых',
            'edit'      =>  'редактирование',
            'addnew'    =>  'добавление',
            'full'      =>  'доступ ко всем полям',
            'activate'  =>  'активирование',
            'close'     =>  'отключение',
        ],
    ],
    'services_welltime' => [
        'name' => 'WellTime',
        'permissions' => [
            'full'    =>  'полный доступ',
            'docs'    =>  'документы',
        ],
    ],
    'services_wellsystem' => [
        'name' => 'WellSystem',
        'permissions' => [
            'full'    =>  'полный доступ',
        ],
    ],
    'services_itpark' => [
        'name' => 'Услуги IT Park\'а',
        'permissions' => [
            'full'    =>  'полный доступ',
        ],
    ],
    'stats' => [
        'name' => 'Статистика',
        'permissions' => [
            'r'                     =>  'просмотр',
            'report'                =>  'отчет',
            'vip_report'            =>  'vip report',
            'voip_recognition'      =>  'телефония-нераспознаные',
            'sale_channel_report'   =>  'региональные представители',
            'onlime_read'           =>  'onlime просмотр отчета',
            'onlime_create'         =>  'onlime создание заявок',
            'onlime_full'           =>  'onlime полный доступ',
        ],
    ],
    'tarifs' => [
        'name' => 'Работа с тарифами',
        'permissions' => [
            'read'    =>  'чтение',
            'edit'    =>  'изменение',
        ],
    ],
    'tt' => [
        'name' => 'Работа с заявками',
        'permissions' => [
            'view'          =>  'просмотр',
            'view_cl'       =>  'показывать "Запросы клиентов"',
            'use'           =>  'использование',
            'time'          =>  'управление временем',
            'admin'         =>  'администраторский доступ',
            'states'        =>  'редактирование состояний',
            'report'        =>  'отчёт',
            'doers_edit'    =>  'редактирование исполнителей',
            'shop_orders'   =>  'заказы магазина',
            'comment'       =>  'коментарии для не своих заявок',
            'rating'        =>  'оценка заявки',
            'limit'         =>  'просмотр остатков',
        ],
    ],
    'usercontrol' => [
        'name' => 'О пользователе',
        'permissions' => [
            'read'          =>  'чтение',
            'edit_pass'     =>  'смена пароля',
            'edit_full'     =>  'изменение всех данных',
            'edit_panels'   =>  'настройка скрытых/открытых панелей (sys)',
            'edit_flags'    =>  'настройка флагов (sys)',
            'dealer'        =>  'дилерский список',
        ],
    ],
    'users' => [
        'name' => 'Работа с пользователями',
        'permissions' => [
            'r'       =>  'чтение',
            'change'  =>  'изменение',
            'grant'   =>  'раздача прав',
        ],
    ],
    'voip' => [
        'name' => 'Телефония',
        'permissions' => [
            'access'  =>  'доступ',
            'admin'   =>  'администрирование',
            'catalog' =>  'справочники',
        ],
    ],
    'voipreports' => [
        'id'   => 'voipreports',
        'name' => 'Телефония Отчеты',
        'permissions' => [
            'access'  =>  'доступ',
            'admin'   =>  'администрирование',
        ],
    ],
];
