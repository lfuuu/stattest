<?php
namespace app\classes;

use Yii;


class Navigation
{
    private $blocks = [];

    private function __construct()
    {
        $this->addBlockForStatModule('clients');
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
        $this->addBlockForStatModule('voipnew');
        $this->addBlockForStatModule('voipreports');
        $this->addBlockForStatModule('ats');
        $this->addBlockForStatModule('data');
        $this->addBlockForStatModule('incomegoods');
        $this->addBlockForStatModule('ats2');
        $this->addBlockForStatModule('logs');
        $this->addBlock(
            NavigationBlock::create()
                ->setId('test_new_clients')
                ->setTitle('Тест Новые клиенты')
                ->addItem('Ссылка 1', '/test/index?xxx')
                ->addItem('Ссылка 2', '/test/index?yyy')
                ->addItem('Ссылка 3', ['test/index', 'zzz'=>'qwe'])
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
        $this->blocks[] = $block;
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
                ->setTitle($title)
            ;
        foreach ($items as $item) {
            $block->addItem($item[0], '?' . $item[1]);
        }

        $this->addBlock($block);
    }

}