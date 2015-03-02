<?php
namespace app\classes;

use Yii;
use app\models\ClientGridSettings;


class Navigation
{
    private $blocks = [];

    private function __construct()
    {
        $this->addBlockForStatModule('clients');
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
          return $this;
        }

        $block =
            NavigationBlock::create()
                ->setId($moduleName)
                ->setTitle($title)
            ;
        foreach ($items as $item) {
            $url =
                substr($item[1], 0, 1) == '/'
                    ? $item[1]
                    : '?' . $item[1];
            $block->addItem($item[0], $url);
        }

        $this->addBlock($block);
        return $this;
    }
    
    private function addBlockNewClients()
    {
        
        $blocks_rows = ClientGridSettings::menuAsArray();
        
        foreach($blocks_rows as $block_row)
        {
            
            $block = NavigationBlock::create()
                ->setId('client_'.$block_row['id'])
                ->setRights(['clients.read'])
                ->setTitle($block_row['name']);
            
            foreach($block_row['items'] as $item)
            {   

                $block->addItem($item['name'],$item['link']);
            }
                    
            $this->addBlock($block);

       
        }
        
    }

}
