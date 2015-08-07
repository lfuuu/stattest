<?php
namespace app\classes;

use app\models\billing\Pricelist;
use app\models\ContractType;
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
        $this->addBlockForStatModule('tarifs');
        $this->addBlockForStatModule('tt');
        $this->addBlockForStatModule('stats');
        $this->addBlockForStatModule('routers');
        $this->addBlockForStatModule('monitoring');
        $this->addBlockForStatModule('users');
        $this->addBlockForStatModule('usercontrol');
        $this->addBlockForStatModule('send');
        $this->addBlockForStatModule('employeers');
        $this->addBlockForStatModule('mail');

        $voipBlock = $this->getBlockForStatModule('voipnew');
        if ($voipBlock) {
            $this->addBlock(
                $voipBlock
                    ->addItem('Плайслисты Клиент Ориг', ['voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 1])
                    ->addItem('Плайслисты Клиент Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_CLIENT, 'orig' => 0])
                    ->addItem('Плайслисты Опер Ориг', ['voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 1])
                    ->addItem('Плайслисты Опер Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_OPERATOR, 'orig' => 0])
                    ->addItem('Плайслисты Местные Терм', ['voip/pricelist/list', 'type' => Pricelist::TYPE_LOCAL, 'orig' => 0])
                    ->addItem('Местные Префиксы', ['voip/network-config/list'])
            );
        }

        $this->addBlockForStatModule('voipreports');
        $this->addBlockForStatModule('ats');
        $this->addBlockForStatModule('data');
        $this->addBlockForStatModule('incomegoods');
        $this->addBlockForStatModule('logs');

        $this->addBlock(
            NavigationBlock::create()
                ->setId('settings')
                ->setTitle('Настройки')
                ->setRights(['organization.read', 'person.read'])
                    ->addItem('Организации', ['/organization'])
                    ->addItem('Ответственные лица', ['/person'])
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

    /**
     * @return NavigationBlock
     */
    private function getBlockForStatModule($moduleName)
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

        return $block;
    }

    private function addBlockForStatModule($moduleName)
    {
        $block = $this->getBlockForStatModule($moduleName);
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
        $blocks_rows = ContractType::find()->orderBy(['sort' => SORT_ASC])->all();

        foreach($blocks_rows as $block_row)
        {
            $block = NavigationBlock::create()
                ->setId('client_'.$block_row->id)
                ->setRights(['clients.read'])
                ->setTitle($block_row->name);

            foreach($block_row->businessProcesses as $item)
            {
                $block->addItem($item->name,
                    isset($exclusion[$item->id])
                    ? $exclusion[$item->id]
                    : Url::toRoute(['client/grid', 'businessProcessId' => $item->id])
                );
            }

            $this->addBlock($block);
        }
    }
    
}
