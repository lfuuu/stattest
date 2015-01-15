<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientGridSettings extends ActiveRecord
{
    public static function tableName()
    {
        return 'grid_settings';
    }
    
    public function getConfigAsArray()
    {
        $xml = $this->config;
        try 
        {
            $xml = simplexml_load_string($xml);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            return $array;
        }
        catch (\yii\base\Exception $e)
        {
            return[];
        }
    }
    
    public static function findDefault($bp_id)
    {
       return self::find()
                ->where(['grid_business_process_id' => $bp_id])
                ->orderBy('default DESC, sort')
                ->one();
    }
    
    public static function findByBP($bp_id)
    {
       return self::find()
                ->where(['grid_business_process_id' => $bp_id])
                ->orderBy('sort')
                ->all();
    }
    
    public static function menuAsArray ()
    {
        $query = new Query();
        $rows = $query->select('ct.id,ct.name')
                    ->from('client_contract_type ct')
                    ->innerJoin('grid_business_process bp', 'bp.client_contract_id = ct.id')
                    ->groupBy('ct.id')
                    ->orderBy('ct.sort')
                    ->all();
        
        foreach($rows as $row)
            $blocks_rows[$row['id']] = $row;
        
        foreach($blocks_rows as $key => $block_row)
        {
            $query = new Query();
            $query->addParams([':id' => $block_row['id']]);
            $blocks_items = $query->select('bp.id, bp.name, link')
                    ->from('grid_business_process bp')
                    ->orderBy('bp.sort')
                    ->where('bp.client_contract_id = :id')
                    ->all();
            
            foreach($blocks_items as $item)
            {
                if ( $item['link'] == null )
                {
                    $item['link'] = '/clients/index?bp='.$item['id'];
                }

                $blocks_rows[$key]['items'][] = $item;
            }
        }
        return $blocks_rows;
    }
    

    
}