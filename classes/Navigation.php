<?php
namespace app\classes;

use Yii;


class Navigation
{
    private $blocks = [];

    private function __construct()
    {
        $this->addBlockForStatModule('clients');
        $this->addBlock(
            NavigationBlock::create()
                ->setId('client_telecom')
                ->setTitle('Телеком')
                    ->addItem('Продажи', '/clients/index?bp=2')
                    ->addItem('Сопровождение', '/clients/index?bp=1')
               // ->addItem('Ссылка 3', ['test/index', 'zzz'=>'qwe'])
        );
        $this->addBlock(
            NavigationBlock::create()
                ->setId('client_ecommerce')
                ->setTitle('Интернет магазин')
                    ->addItem('Заказы магазина', '/?module=tt&action=view_type&type_pk=4')
                    ->addItem('Сопровождение', '/clients/index?bp=4') 

        );
        
       $this->addBlock(
            NavigationBlock::create()
                ->setId('client_procurement')
                ->setTitle('Закупки')
                    ->addItem('Заказы поставщиков', '/?module=tt&action=view_type&type_pk=7')
                    ->addItem('Сопровождение', '/clients/index?bp=6') 

        );
       
        $this->addBlock(
            NavigationBlock::create()
                ->setId('client_operator')
                ->setTitle('Операторы')
                    ->addItem('Сопровождение', '/clients/index?bp=7') 

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
        $this->addBlockForStatModule('voipnew');
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