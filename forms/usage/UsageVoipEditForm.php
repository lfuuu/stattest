<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Event;
use app\models\City;
use app\models\LogTarif;
use app\models\Number;
use app\models\TariffVoip;
use app\models\UsageVoip;
use Yii;
use DateTimeZone;
use DateTime;
use app\models\ClientAccount;
use app\models\TariffNumber;

use yii\helpers\ArrayHelper;

class UsageVoipEditForm extends UsageVoipForm
{
    /** @var ClientAccount */
    public $clientAccount;
    /** @var UsageVoip */
    public $usage;
    /** @var City */
    public $city;
    /** @var DateTimeZone */
    public $timezone;
    /** @var DateTime */
    public $today;
    /** @var DateTime */
    public $tomorrow;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [[
            'type_id', 'connection_point_id', 'city_id', 'client_account_id',
            'no_of_lines', 'did',
            'tariff_main_id', 'tariff_local_mob_id', 'tariff_russia_id', 'tariff_russia_mob_id', 'tariff_intern_id',
        ], 'required', 'on' => 'add'];
        $rules[] = [['did'], 'validateDid', 'on' => 'add'];
        $rules[] = [['address'], 'string', 'on' => 'edit'];
        $rules[] = [[
            'tariff_main_id', 'tariff_local_mob_id', 'tariff_russia_id', 'tariff_russia_mob_id', 'tariff_intern_id',
        ], 'required', 'on' => 'change-tariff'];

        $rules[] = [['number_tariff_id'], 'required', 'on' => 'add', 'when' => function($model) { return $model->type_id == 'number';}];
        return $rules;
    }


    public function add()
    {
        $connectingDate= new DateTime($this->connecting_date, $this->timezone);
        if ($connectingDate < $this->today) {
            $this->addError('connecting_date', 'Дата подключения не может быть в прошлом');
            return false;
        }

        $tariffMain = TariffVoip::findOne($this->tariff_main_id);
        Assert::isObject($tariffMain);

        if ($tariffMain->is_testing) {
            $actualTo = new DateTime($this->connecting_date, $this->timezone);
            $actualTo->modify('+10 days');
            $actualTo = $actualTo->format('Y-m-d');
        } else {
            $actualTo = '2029-01-01';
        }

        $usage = new UsageVoip();
        $usage->region = $this->connection_point_id;
        $usage->actual_from = $this->connecting_date;
        $usage->actual_to = $actualTo;
        $usage->type_id = $this->type_id;
        $usage->client = $this->clientAccount->client;
        $usage->E164 = $this->did;
        $usage->no_of_lines = $this->no_of_lines;
        $usage->status = $this->status;
        $usage->address = $this->address ?: '';
        $usage->edit_user_id = Yii::$app->user->getId();
        $usage->line7800_id = $this->type_id == '7800' ? $this->line7800_id : 0;
        $usage->is_trunk = $this->type_id == 'operator' ? 1 : 0;
        $usage->allowed_direction = 'full';
        $usage->one_sip = 0;
        $usage->is_moved = 0;
        $usage->is_moved_with_pbx = 0;
        $usage->create_params = '{}';

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->saveChangeHistory($usage->oldAttributes, $usage->attributes, 'usage_voip');

            $usage->save();

            $this->saveTariff($usage, $this->connecting_date);

            if ($usage->type_id == 'number') {
                $number = Number::findOne($usage->E164);
                Number::dao()->startActiveStat($number, $usage);
            }

            Event::go('update_phone_product', ['account_id' => $this->clientAccount->id]);
            Event::go('actualize_number', ['number' => $usage->E164]);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $usage->id;

        return true;
    }

    public function edit()
    {
        $this->usage->status = $this->status;
        $this->usage->address = $this->address;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->saveChangeHistory($this->usage->oldAttributes, $this->usage->attributes, 'usage_voip');

            $this->usage->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function changeTariff()
    {
        $tariffChangeDate= new \DateTime($this->tariff_change_date, $this->timezone);
        if ($tariffChangeDate < $this->tomorrow) {
            $this->addError('connecting_date', 'Дата подключения не может быть в прошлом');
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $this->saveTariff($this->usage, $this->tariff_change_date);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function initModel(ClientAccount $clientAccount, UsageVoip $usage = null) {
        $this->clientAccount = $clientAccount;
        $this->client_account_id = $clientAccount->id;
        $this->timezone = $clientAccount->timezone;

        $this->today = new DateTime('now', $this->timezone);
        $this->today->setTime(0, 0, 0);
        $this->tomorrow = new DateTime('tomorrow', $this->timezone);
        $this->tomorrow->setTime(0, 0, 0);

        if ($usage) {
            $this->usage = $usage;
            $this->id = $usage->id;
            $this->connection_point_id = $usage->region;
            $this->tariff_change_date = $this->tomorrow->format('Y-m-d');

            $this->setAttributes($usage->getAttributes(), false);
            $this->did = $usage->E164;

            $currentTariff =
                LogTarif::find()
                    ->andWhere(['service' => 'usage_voip'])
                    ->andWhere(['id_service' => $usage->id])
                    ->andWhere('date_activation<=NOW()')
                    ->andWhere('id_tarif!=0')
                    ->orderBy('date_activation desc, ts desc, id desc')
                    ->limit(1)
                    ->one();

            if ($currentTariff) {
                $this->tariff_main_id = $currentTariff->id_tarif;
                $this->tariff_local_mob_id = $currentTariff->id_tarif_local_mob;
                $this->tariff_russia_id = $currentTariff->id_tarif_russia;
                $this->tariff_russia_mob_id = $currentTariff->id_tarif_russia_mob;
                $this->tariff_intern_id = $currentTariff->id_tarif_intern;

                $this->tariff_group_price = $currentTariff->minpayment_group;
                $this->tariff_group_local_mob_price = $currentTariff->minpayment_local_mob;
                $this->tariff_group_russia_price = $currentTariff->minpayment_russia;
                $this->tariff_group_intern_price = $currentTariff->minpayment_intern;

                $i = 0;
                while ($i < strlen($currentTariff->dest_group)) {
                    $g = $currentTariff->dest_group[$i];
                    if ($g == '5') $this->tariff_group_local_mob = 1;
                    if ($g == '1') $this->tariff_group_russia = 1;
                    if ($g== '2') $this->tariff_group_intern = 1;
                    $i++;
                }

                $tariff = TariffVoip::findOne($this->tariff_main_id);
                $this->tariff_main_status = $tariff->status;
            }

        } else {
            $this->connecting_date = $this->today->format('Y-m-d');
        }
    }

    protected function preProcess() {

        if ($this->city_id) {
            $this->city = City::findOne($this->city_id);
        }

        if (!$this->type_id) {
            $this->type_id = 'number';
        }
    }

    public function processDependenciesNumber()
    {
        if ($this->type_id == 'number') {
            if ($this->number_tariff_id) {
                $numberTariff = TariffNumber::findOne($this->number_tariff_id);
                if ($numberTariff->city_id != $this->city_id) {
                    $this->number_tariff_id = null;
                    $this->did = null;
                } else {

                    if ($this->did) {
                        $number = Number::findOne($this->did);
                        if (!$number || $number->did_group_id != $numberTariff->did_group_id || $number->city_id != $this->city_id) {
                            $this->did = null;
                        }
                    }

                    if (!$this->did) {
                        $number = Number::dao()->getRandomFreeNumber($numberTariff->did_group_id);
                        if ($number) {
                            $this->did = $number->number;
                        }
                    }

                }
            } else {
                $this->did = null;
            }
        }
        if ($this->type_id == 'line') {
            $this->number_tariff_id = null;
            if (strlen($this->did) < 4 || strlen($this->did) > 5) {
                $this->did =
                    Yii::$app->db->createCommand("
                        select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)>=4 and LENGTH(E164)<=5 and E164 not in ('7495', '7499')
                    ")->queryScalar();
            }
        }
        if ($this->type_id == '7800') {
            $this->number_tariff_id = null;
            if (substr($this->did, 0, 4) != '7800') {
                $this->did = null;
            }
        }
        if ($this->type_id == 'operator') {
            $this->number_tariff_id = null;
            if (strlen($this->did) != 3) {
                $this->did =
                    Yii::$app->db->createCommand("
                        select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)=3
                    ")->queryScalar();
            }
        }
    }

    public function processDependenciesTariff()
    {
        if ($this->tariff_local_mob_id) {
            $tariff = TariffVoip::findOne($this->tariff_local_mob_id);
            $this->tariff_group_local_mob_price = $tariff->month_min_payment;
        }
        if ($this->tariff_russia_id) {
            $tariff = TariffVoip::findOne($this->tariff_russia_id);
            $this->tariff_group_russia_price = $tariff->month_min_payment;
        }
        if ($this->tariff_intern_id) {
            $tariff = TariffVoip::findOne($this->tariff_intern_id);
            $this->tariff_group_intern_price = $tariff->month_min_payment;
        }
    }

    public function validateDid($attribute, $params)
    {
        if (!$this->did) {
            return;
        }

        if ($this->type_id == 'number') {
            $number = Number::findOne($this->did);
            if ($number === null) {
                $this->addError('did', 'Номер не найден');
            }

            if ($number && $number->city_id != $this->city_id) {
                $this->addError('did', 'Номер ' . $this->did . ' из другого города');
            }
        }
        if ($this->type_id == 'line') {
            if (!preg_match('/$\d{4,5}^/', $this->did)) {
                $this->addError('did', 'Не верный формат номера');
            }
        }
        if ($this->type_id == 'operator') {
            if (!preg_match('/$\d{3}^/', $this->did)) {
                $this->addError('did', 'Не верный формат номера');
            }
        }
        if ($this->type_id == '7800') {
            if (!preg_match('/$7800\d{7}^/', $this->did)) {
                $this->addError('did', 'Не верный формат номера');
            }
        }

        $actualFrom = $this->connecting_date;
        $actualTo = '2029-01-01';

        $queryVoip =
            UsageVoip::find()
                ->andWhere('(actual_from between :from and :to) or (actual_to between :from and :to)', [':from' => $actualFrom, ':to' => $actualTo])
                ->andWhere(['E164' => $this->did]);
        if ($this->id) {
            $queryVoip->andWhere('id != :id', [':id' => $this->id]);
        }
        foreach ($queryVoip->all() as $usage) {
            $this->addError('did', "Номер пересекается с id: {$usage->id}, клиент: {$usage->clientAccount->client}, c {$usage->actual_from} по {$usage->actual_to}");
        }
    }

    private function saveTariff(UsageVoip $usage, $tariffDate)
    {
        $destGroup = '';
        if ($this->tariff_group_local_mob) $destGroup .= '5';
        if ($this->tariff_group_russia) $destGroup .= '1';
        if ($this->tariff_group_intern) $destGroup .= '2';

        $tariffUsages = [$usage];
        foreach ($tariffUsages as $tariffUsage) {
            $currentTariff =
                LogTarif::find()
                    ->andWhere(['service' => 'usage_voip'])
                    ->andWhere(['id_service' => $tariffUsage->id])
                    ->andWhere('date_activation<=:date', [':date' => $tariffDate])
                    ->andWhere('id_tarif!=0')
                    ->orderBy('date_activation desc, id desc')
                    ->limit(1)
                    ->one();

            $tariffChanged = false;
            if ($this->tariff_main_id != $currentTariff->id_tarif) $tariffChanged = true;
            if ($this->tariff_local_mob_id != $currentTariff->id_tarif_local_mob) $tariffChanged = true;
            if ($this->tariff_russia_id != $currentTariff->id_tarif_russia) $tariffChanged = true;
            if ($this->tariff_russia_mob_id != $currentTariff->id_tarif_russia_mob) $tariffChanged = true;
            if ($this->tariff_intern_id != $currentTariff->id_tarif_intern) $tariffChanged = true;
            if ($this->connecting_date != $currentTariff->date_activation) $tariffChanged = true;
            if ($destGroup != $currentTariff->dest_group) $tariffChanged = true;
            if ($this->tariff_group_price != $currentTariff->minpayment_group) $tariffChanged = true;
            if ($this->tariff_group_local_mob_price != $currentTariff->minpayment_local_mob) $tariffChanged = true;
            if ($this->tariff_group_russia_price != $currentTariff->minpayment_russia) $tariffChanged = true;
            if ($this->tariff_group_intern_price != $currentTariff->minpayment_intern) $tariffChanged = true;

            if ($tariffChanged) {
                $this->logTarifUsage('usage_voip',
                    $tariffUsage->id, $tariffDate,
                    $this->tariff_main_id, $this->tariff_local_mob_id, $this->tariff_russia_id, $this->tariff_russia_mob_id, $this->tariff_intern_id,
                    $destGroup, $this->tariff_group_price,
                    $this->tariff_group_local_mob_price, $this->tariff_group_russia_price, $this->tariff_group_intern_price
                );
            }
        }
    }

    public function getLinesFor7800(ClientAccount $clientAccount)
    {
        $query =
            UsageVoip::find()
                ->andWhere(['client' => $clientAccount->client])
                ->andWhere('LENGTH(E164) >= 4 and LENGTH(E164) <= 6')
                ->andWhere('actual_to > DATE(now())');
            ;
        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('id')
                    ->asArray()
                    ->all(),
                'id',
                'E164'
            );

        return $list;
    }

    private function saveChangeHistory($cur = array(), $new = array(), $usage_name = '')
    {
        if (!$cur || count($cur) == 0 || count($new) == 0 || !strlen($usage_name))
            return;

        $fields = array();
        foreach ($cur as $k=>$v) {
            if (isset($new[$k]) && $new[$k] != $v) {
                $fields[$k] = array('value_from'=>$v, 'value_to'=>$new[$k]);
            }
        }
        if (!count($fields)) return;

        Yii::$app->db->createCommand("
            insert into log_usage_history(service, service_id, user_id) values (:service, :serviceId, :userId)
        ", [
            'service' => $usage_name,
            'serviceId' => $cur['id'],
            'userId' => Yii::$app->user->id,
        ])->execute();

        if ($log_usage_history_id = Yii::$app->db->lastInsertID) {
            foreach ($fields as $field => $v) {
                Yii::$app->db->createCommand("
                    insert into log_usage_history_fields(log_usage_history_id, field, value_from, value_to) values (:id, :field, :valueFrom, :valueTo)
                ", [
                    'id' => $log_usage_history_id,
                    'field' => $field,
                    'valueFrom' => $v['value_from'],
                    'valueTo' => $v['value_to'],
                ])->execute();
            }
        }
    }

    private function logTarifUsage($service,$id,$dateActivation,
                                         $tarifId,$tarifLocalMobId,$tarifRussiaId,$tarifRussiaMobId,$tarifInternId,
                                         $dest_group, $minpayment_group,
                                         $minpayment_local_mob, $minpayment_russia, $minpayment_intern)
    {
        Yii::$app->db->createCommand(
            'insert into log_tarif (service,id_service,id_user,ts,date_activation,comment,
                                        id_tarif,id_tarif_local_mob,id_tarif_russia,id_tarif_russia_mob,id_tarif_intern,
                                        dest_group,minpayment_group,
                                        minpayment_local_mob,minpayment_russia,minpayment_intern
                                    ) VALUES '.
            '("'.$service.'",'.$id.','.Yii::$app->user->id.',NOW(),"'.addslashes($dateActivation).'","",'.
            intval($tarifId).','.intval($tarifLocalMobId).','.intval($tarifRussiaId).','.intval($tarifRussiaMobId).','.intval($tarifInternId).','.
            intval($dest_group).','.intval($minpayment_group).','.
            intval($minpayment_local_mob).','.intval($minpayment_russia).','.intval($minpayment_intern).
            ')')->execute();

    }
}