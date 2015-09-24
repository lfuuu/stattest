<?php
namespace app\classes;

use app\models\billing\Pricelist;
use app\models\BusinessProcess;
use app\models\Business;
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
                ->addItem('Новый клиент',Url::toRoute(['client/create']), 'clients.read')
                ->addItem('Мои клиенты',Url::toRoute([
                    'client/search',
                    'manager' => Yii::$app->user->identity->user,
                    'account_manager' => Yii::$app->user->identity->user
                ]), 'clients.read')
                ->addItem('Каналы продаж', '/sale-channel/index', 'clients.edit')
                ->addItem('Отчет по файлам', '/file/report', 'clients.edit')
        );
        $this->addBlockNewClients();

        $this->addBlockForStatModule('services');
        $this->addBlockForStatModule('newaccounts');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Тарифы')
                ->addItem('Телефония', ['tariff/voip'], ['tarifs.read'])
                ->addItem('Телефония Пакеты', ['tariff/voip-package'], ['tarifs.read'])
                ->addStatModuleItems('tarifs')
                ->addItem('Телефония DID группы', ['tariff/did-group/list'], ['tarifs.read'])
                ->addItem('Телефония Номера', ['tariff/number/index'], ['tarifs.read'])
        );
        $this->addBlockForStatModule('tt');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Статистика')
                ->addStatModuleItems('stats')
                ->addItem('Отчет по OnLime', ['reports/onlime-report'], ['stats.report'])
                ->addItem('Отчет по OnLime оборудование', ['reports/onlime-devices-report'], ['stats.report'])
                ->addItem('Состояние номеров', ['usage/number/detail-report'], ['stats.report'])
        );
        $this->addBlockForStatModule('routers');
        $this->addBlockForStatModule('monitoring');
        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Управление доступом')
                ->addItem('Операторы', ['user/control'], ['users.r'])
                ->addItem('Группы', ['user/group'], ['users.r'])
                ->addItem('Отделы', ['user/department'], ['users.r'])
                ->addItem('Обновить права в БД', ['user/control/update-rights'], ['users.r'])
        );
        $this->addBlockForStatModule('send');
        $this->addBlockForStatModule('employeers');
        $this->addBlockForStatModule('mail');

        $this->addBlock(
            NavigationBlock::create()
                ->setTitle('Телефония')
                ->addStatModuleItems('voipnew')
                ->addItem('Прайс-листы Клиент Ориг', ['voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 1])
                ->addItem('Прайс-листы Клиент Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 0])
                ->addItem('Прайс-листы Опер Ориг', ['voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 1])
                ->addItem('Прайс-листы Опер Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 0])
                ->addItem('Прайс-листы Местные Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_LOCAL, 'orig' => 0])
                ->addItem('Местные Префиксы', ['voip/network-config/list'])
                ->addItem('Списки префиксов', ['voip/prefixlist'])
                ->addItem('Направления', ['voip/destination'])
        );

        $this->addBlockForStatModule('voipreports');
        $this->addBlockForStatModule('ats');
        $this->addBlockForStatModule('data');
        $this->addBlockForStatModule('incomegoods');
        $this->addBlockForStatModule('logs');

        $settingsBlock = NavigationBlock::create();
        if ($settingsBlock) {
            $this->addBlock(
                $settingsBlock
                    ->setId('settings')
                    ->setTitle('Настройки')
                    ->setRights(['organization.read', 'person.read'])
                    ->addItem('Организации', ['/organization'])
                    ->addItem('Ответственные лица', ['/person'])
            );
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
                Business::tableName().'.sort' => SORT_ASC,
                BusinessProcess::tableName().'.sort' => SORT_ASC,
            ])
            ->all();

        foreach($businesses as $business)
        {
            $block = NavigationBlock::create()
                ->setId('client_'.$business->id)
                ->setRights(['clients.read'])
                ->setTitle($business->name);

            foreach($business->businessProcesses as $process)
            {
                $block->addItem($process->name,
                    isset($exclusion[$process->id])
                    ? $exclusion[$process->id]
                    : Url::toRoute(['client/grid', 'businessProcessId' => $process->id])
                );
            }

            $this->addBlock($block);
        }
    }
    
}
