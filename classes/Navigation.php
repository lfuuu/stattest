<?php
namespace app\classes;

use Yii;
use app\models\ClientGridSettings;
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
                ->addItem('Каналы продаж', '/?module=clients&action=sc', 'clients.sale_channels')
                ->addItem('Отчет по файлам', '/?module=clients&action=files_report', 'clients.file')
        );

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
                    ->addItem('Плайслисты Оригинация', ['voip/pricelist/list', 'local' => 0, 'orig' => 1])
                    ->addItem('Плайслисты Терминация', ['voip/pricelist/list', 'local' => 0, 'orig' => 0])
                    ->addItem('Плайслисты Местные', ['voip/pricelist/list', 'local' => 1, 'orig' => 0])
                    ->addItem('Местные Префиксы', ['voip/network-config/list'])
            );
        }

        $this->addBlockForStatModule('voipreports');
        $this->addBlockForStatModule('ats');
        $this->addBlockForStatModule('data');
        $this->addBlockForStatModule('incomegoods');
        $this->addBlockForStatModule('ats2');
        $this->addBlockForStatModule('logs');
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
    
}
