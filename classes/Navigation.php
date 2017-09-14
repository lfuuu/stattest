<?php

namespace app\classes;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\Pricelist;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\Country;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;


/**
 * Class Navigation
 */
class Navigation
{
    private $_blocks = [];

    /**
     * Navigation constructor.
     *
     * @throws \yii\base\InvalidParamException
     */
    private function __construct()
    {
        global $fixclient_data;

        $this->addBlock(
            NavigationBlock::create()
                ->setRights(['clients.read'])
                ->setTitle('Клиенты')
                ->addItem('Новый клиент', Url::toRoute(['/client/create']), 'clients.read')
                ->addItem('Мои клиенты', Url::toRoute([
                    '/client/search',
                    'manager' => Yii::$app->user->identity->user,
                    'account_manager' => Yii::$app->user->identity->user
                ]), 'clients.read')
                ->addItem('Каналы продаж', '/sale-channel/index', 'clients.edit')
                ->addItem('Отчет по файлам', '/file/report', 'clients.edit')
        );
        $this->_addBlockNewClients();

        $this->_addBlockForStatModule('services');

        $accountBlock = NavigationBlock::create()
            ->setTitle('Бухгалтерия')
            ->addStatModuleItems('newaccounts');

        if ($fixclient_data) {
            $accountBlock->addItem('Перенос эл. платежей', '/payment/yandex-transfer', 'newaccounts_payments.delete');
        }

        $accountBlock->addItem('Реестр неоплаченных счетов поставщиков', '/report/operator-pay/', 'clients.edit');
        $accountBlock->addItem('Платежи',
            [
                '/report/accounting/pay-report/',
                'PayReportFilter[add_date_from]' => (new \DateTimeImmutable)->modify('-2 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            ],
            'newaccounts_payments.read'
        );

        $this->addBlock($accountBlock);


        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Тарифы')
                ->addItem('Телефония', ['/tariff/voip'], ['tarifs.read'])
                ->addItem('Телефония Пакеты', ['/tariff/voip-package'], ['tarifs.read'])
                ->addItem('Звонок_чат', ['/tariff/call-chat'], ['tarifs.read'])
                ->addStatModuleItems('tarifs')
        );
        $this->_addBlockForStatModule('tt');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Статистика')
                ->addStatModuleItems('stats')
                ->addItem('Отчёт по файлам', ['/file/report'], ['stats.report'])
                ->addItem('Отчет по OnLime', ['/reports/onlime-report'], ['stats.report'])
                ->addItem('Отчет по OnLime оборудование', ['/reports/onlime-devices-report'], ['stats.report'])
                ->addItem('Себестоимость звонков', ['/report/voip/cost-report'], ['stats.report'])
        );
        $this->_addBlockForStatModule('routers');

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Мониторинг')
                ->addStatModuleItems('monitoring')
                ->addItem('Перемещаемые услуги', ['/monitoring/transfered-usages'], [])
                ->addItem('Ключевые события', ['/monitoring'], [])
                ->addItem('Очередь событий', ['/monitoring/event-queue'], [])
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Управление доступом')
                ->addItem('Операторы', ['/user/control'], ['users.r'])
                ->addItem('Группы', ['/user/group'], ['users.r'])
                ->addItem('Отделы', ['/user/department'], ['users.r'])
                ->addItem('Обновить права в БД', ['/user/control/update-rights'], ['users.r'])
        );
        $this->_addBlockForStatModule('send');
        $this->_addBlockForStatModule('employeers');

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Письма клиентам')
                ->addStatModuleItems('mail')
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Телефония')
                ->addStatModuleItems('voipnew')
                ->addItem('Прайс-листы Клиент Ориг', ['/voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 1], ['voip.access'])
                ->addItem('Прайс-листы Клиент Терм', ['/voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 0], ['voip.access'])
                ->addItem('Прайс-листы Опер Ориг', ['/voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 1], ['voip.access'])
                ->addItem('Прайс-листы Опер Терм', ['/voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 0], ['voip.access'])
                ->addItem('Прайс-листы Местные Терм', ['/voip/pricelist/list', 'type' => Pricelist::TYPE_LOCAL, 'orig' => 0], ['voip.access'])
                ->addItem('Местные Префиксы', ['/voip/network-config/list'], ['voip.access'])
                ->addItem('Списки префиксов', ['/voip/prefixlist'], ['voip.access'])
                ->addItem('Направления', ['/voip/destination'], ['voip.access'])
                ->addItem('DID группы', ['/tariff/did-group/'], ['tarifs.read'])
                ->addItem('Номера', ['/voip/number'], ['stats.report'])
                ->addItem('Реестр номеров', ['/voip/registry'], ['voip.access'])
                ->addItem('Отчет по calls_raw', ['/voip/raw'], ['voip.access'])
                ->addItem('Статистика (4 класс + 5 класс)', ['/voip/combined-statistics'], ['voip.access'])
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Межоператорка (Отчеты)')
                ->addStatModuleItems('voipreports')
                ->addItem('Загруженность номеров', ['/voipreport/cdr-workload'], ['voipreports.access'])
        );

        // $this->addBlockForStatModule('voipreports');
        $this->_addBlockForStatModule('ats');
        $this->_addBlockForStatModule('data');
        $this->_addBlockForStatModule('incomegoods');

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Логи')
                ->addStatModuleItems('logs')
                ->addItem('Значимые события', ['/important_events/report'])
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setId('dictionaries')
                ->setTitle('Словари')
                ->addItem('Организации', ['/organization'], ['organization.read'])
                ->addItem('Ответственные лица', ['/person'], ['person.read'])
                ->addItem('Названия событий', ['/important_events/names'], ['dictionary-important-event.important-events-names'])
                ->addItem('Группы событий', ['/important_events/groups'], ['dictionary-important-event.important-events-groups'])
                ->addItem('Источники событий', ['/important_events/sources'], ['dictionary-important-event.important-events-sources'])
                ->addItem('Страны', ['/dictionary/country/'], ['dictionary.read'])
                ->addItem('Города', ['/dictionary/city/'], ['dictionary.read'])
                ->addItem('Регионы (точки подключения)', ['/dictionary/region/'], ['dictionary.read'])
                ->addItem('Методы биллингования', ['/dictionary/city-billing-methods/'], ['dictionary.read'])
                ->addItem('Настройки платежных документов', ['/dictionary/invoice-settings'], ['dictionary.read'])
                ->addItem('Точка входа', ['/dictionary/entry-point'], ['dictionary.read'])
                ->addItem('Публичные сайты', ['/dictionary/public-site'], ['dictionary.read'])
                ->addItem('Метки', ['/dictionary/tags'], ['dictionary.read'])
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setId('templates')
                ->setTitle('Шаблоны')
                ->addItem('Договоры', ['/templates/document/template'])
                ->addItem('Универсальные счета-фактуры', ['/templates/uu/invoice'], ['newaccounts_balance.read'])
        );

        $this->_addBlockUniversalUsage();

        $this->addBlock(
            NavigationBlock::create()
                ->setId('nnp')
                ->setTitle('Национальный номерной план')
                ->addItem('Диапазон номеров', ['/nnp/number-range/', 'NumberRangeFilter[country_code]' => Country::RUSSIA, 'NumberRangeFilter[is_active]' => 1], ['tarifs.read'])
                ->addItem('Операторы', ['/nnp/operator/'], ['tarifs.read'])
                ->addItem('Страны', ['/nnp/country/'], ['tarifs.read'])
                ->addItem('Регионы', ['/nnp/region/'], ['tarifs.read'])
                ->addItem('Города', ['/nnp/city/'], ['tarifs.read'])
                ->addItem('Префиксы', ['/nnp/prefix/'], ['tarifs.read'])
                ->addItem('Типы NDC', ['/nnp/ndc-type/'], ['tarifs.read'])
                ->addItem('Направления', ['/nnp/destination/'], ['tarifs.read'])
                ->addItem('Территории направлений', ['/nnp/land/'], ['tarifs.read'])
                ->addItem('Статусы направлений', ['/nnp/status/'], ['tarifs.read'])
                ->addItem('Импорт', ['/nnp/import/'], ['tarifs.read'])
        );

        /** @var \app\modules\notifier\Module $module */
        if ($module = Yii::$app->getModule('notifier')) {
            $module->getNavigation($this);
        }
    }

    /**
     * @return Navigation
     */
    public static function create()
    {
        if (!function_exists('access')) {
            include_once Yii::$app->basePath . '/classes/compatibility.php';
        }

        return new self();
    }

    /**
     * @return NavigationBlock[]
     */
    public function getBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Добавление блока навигации
     *
     * @param NavigationBlock $block
     * @return $this
     */
    public function addBlock(NavigationBlock $block)
    {
        if (!$block->id) {
            $block->id = 'block' . md5($block->title);
        }

        if (empty($block->items)) {
            return $this;
        }

        if ($block->rights) {
            foreach ($block->rights as $right) {
                if (Yii::$app->user->can($right)) {
                    $this->_blocks[] = $block;
                    break;
                }
            }
        } else {
            $this->_blocks[] = $block;
        }

        return $this;
    }

    /**
     * Добавление навигации из статовского модуля
     *
     * @param string $moduleName
     * @return $this|null
     */
    private function _addBlockForStatModule($moduleName)
    {
        $statModule = StatModule::getHeadOrModule($moduleName);

        list($title, $items) = $statModule->GetPanel(null);

        if (!$title || !$items) {
            return null;
        }

        $block = NavigationBlock::create()
            ->setId($moduleName)
            ->setTitle($title);

        foreach ($items as $item) {
            $url = substr($item[1], 0, 1) == '/' ? $item[1] : '?' . $item[1];
            $block->addItem($item[0], $url);
        }

        if ($block !== null) {
            $this->addBlock($block);
        }

        return $this;
    }

    /**
     * Add Block New Clients
     */
    private function _addBlockNewClients()
    {
        $exclusion = [
            2 => '?module=tt&action=view_type&type_pk=8',
            3 => '?module=tt&action=view_type&type_pk=4',
            5 => '/?module=tt&action=view_type&type_pk=7',
        ];
        $businesses = Business::find()
            ->innerJoinWith('businessProcesses')
            ->orderBy([
                Business::tableName() . '.sort' => SORT_ASC,
                BusinessProcess::tableName() . '.sort' => SORT_ASC,
            ])
            ->all();

        foreach ($businesses as $business) {
            $block = NavigationBlock::create()
                ->setId('client_' . $business->id)
                ->setRights(['clients.read'])
                ->setTitle($business->name);

            foreach ($business->businessProcesses as $process) {
                $block->addItem($process->name,
                    isset($exclusion[$process->id]) ?
                        $exclusion[$process->id] :
                        Url::toRoute(['client/grid', 'businessProcessId' => $process->id])
                );
            }

            $this->addBlock($block);
        }
    }

    /**
     * Добавить меню универсальных услуг (тарифы, услуги, мониторинг)
     *
     * @throws \yii\base\InvalidParamException
     */
    private function _addBlockUniversalUsage()
    {
        // тарифы
        $block = NavigationBlock::create();
        $block->setTitle(Yii::t('tariff', 'Universal tariffs'));

        // услуги
        $block2 = NavigationBlock::create();
        $block2->setTitle(Yii::t('tariff', 'Universal services'));

        // тарифы и услуги
        $serviceTypes = ServiceType::find()
            ->orderBy(new Expression('COALESCE(parent_id, id), id')) // чтобы пакеты были рядом с базовым тарифом
            ->all();
        foreach ($serviceTypes as $serviceType) {

            $block->addItem($serviceType->name, Url::to([
                '/uu/tariff',
                'serviceTypeId' => $serviceType->id,
            ]), ['tarifs.read']);

            if (array_key_exists($serviceType->id, ServiceType::$packages)) {
                // для пакетов услуги подключаются через базовую услугу
                continue;
            }

            $block2->addItem($serviceType->name, Url::to([
                '/uu/account-tariff',
                'serviceTypeId' => $serviceType->id,
                // 'AccountTariffFilter[tariff_period_id]' => TariffPeriod::IS_SET,
            ]), ['tarifs.read']);

        }

        $this->addBlock($block);
        $this->addBlock($block2);

        // Универсальный тарификатор
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle(Yii::t('tariff', 'Universal tarifficator'))
                ->addItem(Yii::t('tariff', 'Tariff statuses'), ['/uu/tariff-status'], ['tarifs.read'])
                ->addItem(Yii::t('tariff', 'Service types'), ['/uu/service-type'], ['tarifs.read'])
                ->addItem(Yii::t('tariff', 'Setup tariffication'), ['/uu/account-log/setup'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Period tariffication'), ['/uu/account-log/period'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Resource tariffication'), ['/uu/account-log/resource'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Min resource tariffication'), ['/uu/account-log/min'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Account entries'), ['/uu/account-entry'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Bills'), ['/uu/bill'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Invoice'), ['/uu/invoice/view'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Balance'), ['/uu/balance/view'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Clear UU-calls'), ['/uu/resource/clear'], ['services_voip.edit'])
                ->addItem(Yii::t('tariff', 'Monitoring'), [
                    '/uu/monitor',
                    'AccountLogMonitorFilter[tariff_period_id]' => TariffPeriod::IS_SET,
                    'AccountLogMonitorFilter[month]' => date('Y-m'),
                ],
                    ['newaccounts_balance.read'])
        );
    }

}
