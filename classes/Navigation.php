<?php
namespace app\classes;

use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
use app\models\billing\Pricelist;
use app\models\Business;
use app\models\BusinessProcess;
use Yii;
use yii\helpers\Url;


class Navigation
{
    private $blocks = [];

    private function __construct()
    {
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
        $this->addBlockNewClients();

        $this->addBlockForStatModule('services');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Бухгалтерия')
                ->addStatModuleItems('newaccounts')
        );
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Тарифы')
                ->addItem('Телефония', ['/tariff/voip'], ['tarifs.read'])
                ->addItem('Телефония Пакеты', ['/tariff/voip-package'], ['tarifs.read'])
                ->addItem('Звонок_чат', ['/tariff/call-chat'], ['tarifs.read'])
                ->addStatModuleItems('tarifs')
                ->addItem('Телефония DID группы', ['/tariff/did-group/'], ['tarifs.read'])
                ->addItem('Телефония Номера', ['/tariff/number/index'], ['tarifs.read'])
        );
        $this->addBlockForStatModule('tt');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Статистика')
                ->addStatModuleItems('stats')
                ->addItem('Отчёт по файлам', ['/file/report'], ['stats.report'])
                ->addItem('Отчет по OnLime', ['/reports/onlime-report'], ['stats.report'])
                ->addItem('Отчет по OnLime оборудование', ['/reports/onlime-devices-report'], ['stats.report'])
                ->addItem('Себестоимость звонков', ['/report/voip/cost-report'], ['stats.report'])
        );
        $this->addBlockForStatModule('routers');

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
        $this->addBlockForStatModule('send');
        $this->addBlockForStatModule('employeers');

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
                ->addItem('Тип номера', ['/voip/number-type'], ['voip.access'])
                ->addItem('Номера', ['/voip/number'], ['stats.report'])
                ->addItem('Реестр номеров', ['/voip/registry'], ['voip.access'])
        );

        $this->addBlockForStatModule('voipreports');
        $this->addBlockForStatModule('ats');
        $this->addBlockForStatModule('data');
        $this->addBlockForStatModule('incomegoods');

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
                ->addItem('Названия событий', ['/important_events/names'])
                ->addItem('Группы событий', ['/important_events/groups'])
                ->addItem('Источники событий', ['/important_events/sources'])
                ->addItem('Правила на события', ['/important_events/rules'])
                ->addItem('Страны', ['/dictionary/country/'])
                ->addItem('Города', ['/dictionary/city/'])
                ->addItem('Методы биллингования', ['/dictionary/city-billing-methods/'])
                ->addItem('Настройки платежных документов', ['/dictionary/invoice-settings'])
                ->addItem('Точка входа', ['/dictionary/entry-point'])
        );

        $this->addBlock(
            NavigationBlock::create()
                ->setId('templates')
                ->setTitle('Шаблоны')
                ->addItem('Договоры', ['/templates/document/template'])
                ->addItem('Почтовые оповещения', ['/templates/email/template'], ['mail.w'])
                ->addItem('Универсальные счета-фактуры', ['/templates/uu/invoice'], ['newaccounts_balance.read'])
        );

        $this->addBlockUniversalUsage();

        $this->addBlock(
            NavigationBlock::create()
                ->setId('nnp')
                ->setTitle('Национальный номерной план')
                ->addItem('Диапазон номеров', ['/nnp/number-range/'], ['tarifs.read'])
                ->addItem('Операторы', ['/nnp/operator/'], ['tarifs.read'])
                ->addItem('Регионы', ['/nnp/region/'], ['tarifs.read'])
                ->addItem('Префиксы', ['/nnp/prefix/'], ['tarifs.read'])
                ->addItem('Типы NDC', ['/nnp/ndc-type/'], ['tarifs.read'])
                ->addItem('Направления', ['/nnp/destination/'], ['tarifs.read'])
                ->addItem('Пакеты', Url::to(['/uu/tariff', 'serviceTypeId' => ServiceType::ID_VOIP_PACKAGE]), ['tarifs.read'])
        );
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
        return $this->blocks;
    }

    private function addBlock(NavigationBlock $block)
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
                    $this->blocks[] = $block;
                    break;
                }
            }
        } else {
            $this->blocks[] = $block;
        }

        return $this;
    }

    private function addBlockForStatModule($moduleName)
    {
        $statModule = StatModule::getHeadOrModule($moduleName);

        list($title, $items) = $statModule->GetPanel(null);

        if (!$title || !$items) {
            return null;
        }

        $block =
            NavigationBlock::create()
                ->setId($moduleName)
                ->setTitle($title);
        foreach ($items as $item) {
            $url =
                substr($item[1], 0, 1) == '/'
                    ? $item[1]
                    : '?' . $item[1];
            $block->addItem($item[0], $url);
        }

        if ($block !== null) {
            $this->addBlock($block);
        }
        return $this;
    }

    private function addBlockNewClients()
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
                    isset($exclusion[$process->id])
                        ? $exclusion[$process->id]
                        : Url::toRoute(['client/grid', 'businessProcessId' => $process->id])
                );
            }

            $this->addBlock($block);
        }
    }

    /**
     * Добавить меню универсальных услуг (тарифы, услуги, мониторинг)
     */
    private function addBlockUniversalUsage()
    {
        // тарифы
        $block = NavigationBlock::create();
        $block->setTitle(Yii::t('tariff', 'Universal tariffs'));

        // услуги
        $block2 = NavigationBlock::create();
        $block2->setTitle(Yii::t('tariff', 'Universal services'));

        // типы услуг в тарифах и услугах
        $serviceTypes = ServiceType::find()->all();
        foreach ($serviceTypes as $serviceType) {

            $block->addItem($serviceType->name, Url::to([
                '/uu/tariff',
                'serviceTypeId' => $serviceType->id,
            ]), ['tarifs.read']);

            $block2->addItem($serviceType->name, Url::to([
                '/uu/account-tariff',
                'serviceTypeId' => $serviceType->id,
//                'AccountTariffFilter[tariff_period_id]' => TariffPeriod::IS_SET,
            ]), ['tarifs.read']);

        }

        $this->addBlock($block);
        $this->addBlock($block2);

        // мониторинг
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle(Yii::t('tariff', 'Universal tarifficator'))
                ->addItem(Yii::t('tariff', 'Setup tariffication'), ['/uu/account-log/setup'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Period tariffication'), ['/uu/account-log/period'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Resource tariffication'), ['/uu/account-log/resource'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Min resource tariffication'), ['/uu/account-log/min'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Monitoring'), [
                    'uu/monitor',
                    'AccountLogMonitorFilter[tariff_period_id]' => TariffPeriod::IS_SET,
                    'AccountLogMonitorFilter[month]' => date('Y-m'),
                ],
                    ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Account entries'), ['/uu/account-entry'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Bills'), ['/uu/bill'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Invoice'), ['/uu/invoice/view'], ['newaccounts_balance.read'])
                ->addItem(Yii::t('tariff', 'Balance'), ['/uu/balance/view'], ['newaccounts_balance.read'])
        );
    }

}
