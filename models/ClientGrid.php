<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientGrid extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_bp_grid';
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
            return;
        }
    }
    
    public static function findDefault($bp_id)
    {
       return self::find()
                ->where(['client_bp_id' => $bp_id])
                ->orderBy('default DESC, sort')
                ->one();
    }
    
    public static function findByBP($bp_id)
    {
       return self::find()
                ->where(['client_bp_id' => $bp_id])
                ->orderBy('sort')
                ->all();
    }
    

    
}