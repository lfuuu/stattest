<?php
namespace app\models;

use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use yii\db\ActiveRecord;
use app\classes\behaviors\LogClientContractTypeChange;
use app\models\ClientGridSettings;
use app\models\ClientBPStatuses;

/**
 * @property int $id
 * @property string $client
 * @property int $nds_zero

 * @property ClientSuper $superClient
 * @property ClientStatuses $lastComment
 * @property Region $accountRegion
 * @property
 */
class ClientAccount extends ActiveRecord
{
    public static $statuses = array(
        'negotiations'        => array('name'=>'в стадии переговоров','color'=>'#C4DF9B'),
        'testing'             => array('name'=>'тестируемый','color'=>'#6DCFF6'),
        'connecting'          => array('name'=>'подключаемый','color'=>'#F49AC1'),
        'work'                => array('name'=>'включенный','color'=>''),
        'closed'              => array('name'=>'отключенный','color'=>'#FFFFCC'),
        'tech_deny'           => array('name'=>'тех. отказ','color'=>'#996666'),
        'telemarketing'       => array('name'=>'телемаркетинг','color'=>'#A0FFA0'),
        'income'              => array('name'=>'входящие','color'=>'#CCFFFF'),
        'deny'                => array('name'=>'отказ','color'=>'#A0A0A0'),
        'debt'                => array('name'=>'отключен за долги','color'=>'#C00000'),
        'double'              => array('name'=>'дубликат','color'=>'#60a0e0'),
        'trash'               => array('name'=>'мусор','color'=>'#a5e934'),
        'move'                => array('name'=>'переезд','color'=>'#f590f3'),
        'suspended'           => array('name'=>'приостановленные','color'=>'#C4a3C0'),
        'denial'              => array('name'=>'отказ/задаток','color'=>'#00C0C0'),
        'once'                => array('name'=>'Интернет Магазин','color'=>'silver'),
        'reserved'            => array('name'=>'резервирование канала','color'=>'silver'),
        'blocked'             => array('name'=>'временно заблокирован','color'=>'silver'),
        'distr'               => array('name'=>'Поставщик','color'=>'yellow'),
        'operator'            => array('name'=>'Оператор','color'=>'lightblue')
    );

    private $_lastComment = false;

    public static function tableName()
    {
        return 'clients';
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            LogClientContractTypeChange::className()
            ];
    }

    public function getTaxRate()
    {
        return $this->nds_zero ? 0 : 0.18;
    }

    public function getSuperClient()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }

    public function getContractType()
    {
        return $this->hasOne(ClientContractType::className(), ['id' => 'contract_type_id']);
    }

    public function getAccountRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getStatusName()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['name']
                : $this->status;
    }

    public function getStatusColor()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['color']
                : '';
    }

    public function getLastComment()
    {
        if ($this->_lastComment === false) {
            $this->_lastComment =
                ClientStatuses::find()
                    ->andWhere(['id_client' => $this->id])
                    ->andWhere('comment != ""')
                    ->orderBy('ts desc')
                    ->one();
        }
        return $this->_lastComment;
    }
    
    public function getBusinessProcessStatus()
    {
        return $this->hasMany(ClientGridSettings::className(), ['id' => 'grid_status_id'])
            ->viaTable('client_grid_statuses', ['client_id' => 'id'])
            ->one();
    }
    
    public function setBusinessProcessStatus($grid_status_id)
    {         
          /* $model = ClientBPStatuses::find()
                          ->andWhere(['client_id' => $this->id])
                          ->one();*/
        
           $model = ClientBPStatuses::findOne(['client_id' => $this->id]);

           if($model === null)
           {
               $model = new ClientBPStatuses();
           }
        
           $model->grid_status_id = $grid_status_id;
           $model->client_id = $this->id;
           $model->save();
           
           $cs = new ClientStatuses();

           $cs->ts = date("Y-m-d H:i:s");
           $cs->id_client = $this->id;
           $cs->user = \Yii::$app->user->getIdentity()->user;
           $cs->status = "";
           $cs->comment = "Установлен статус бизнес процесса: ".  ClientGridSettings::findOne($grid_status_id)->name;
           $cs->save();
         
    }
}
