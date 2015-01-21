<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientBPStatuses extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_grid_statuses';
    }
    
    static public function statusesByClientContractType($client_contract_type_id)
    {
        $query = new Query();
        return    $query->select('g.id as status_id, g.name as status_name, bp.id as bp_id, bp.name as bp_name')
                        ->from('grid_settings g')
                        ->innerjoin('grid_business_process bp','bp.id = g.grid_business_process_id')
                        ->andwhere('bp.client_contract_id='.$client_contract_type_id)
                        ->andwhere('g.show_as_status>0')
                        ->orderBy('bp.id, g.sort')
                        ->all();
    }
    
    static public function attachStatuses($client_id, Array $statuses_ids)
    {
       foreach($statuses_ids as $status_id)
       {
           $model = new static();
           $model->grid_status_id = $status_id;
           $model->client_id = $client_id;
           $model->save();
       }
    }
}
