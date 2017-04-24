<?php

namespace app\classes;

use app\classes\api\ApiPhone;
use app\models\ActualNumber;
use app\models\Region;
use app\models\UsageVoip;

/**
 * Class ActaulizerVoipNumbers
 *
 * @method static ActaulizerVoipNumbers me($args = null)
 */
class ActaulizerVoipNumbers extends Singleton
{
    /**
     * @param string $number
     * @return bool
     */
    public function actualizeByNumber($number)
    {
        if ($this->_check7800($number)) {
            return true;
        }

        $this->_checkSync($number);
    }

    /**
     * @param int $clientId
     */
    public function actualizeByClientId($clientId)
    {
        $this->_checkSync(null, $clientId);
    }

    /**
     * Актуализировать всё
     */
    public function actualizeAll()
    {
        $this->_checkSync();
    }

    /**
     * @param string $number
     * @param int $clientId
     */
    private function _checkSync($number = null, $clientId = null)
    {
        if (
        $diff = $this->_checkDiff(
            ActualNumber::dao()->loadSaved($number, $clientId),
            ActualNumber::dao()->collectFromUsages($number, $clientId)
        )
        ) {
            $this->_diffToSync($diff);
        }

    }

    /**
     * @param string $number
     * @throws \Exception
     */
    public function sync($number = null)
    {
        if (!$number) {
            return;
        }

        if (
        $diff = $this->_diff(
            ActualNumber::dao()->loadSaved($number),
            ActualNumber::dao()->collectFromUsages($number)
        )
        ) {
            $transaction = ActualNumber::getDb()->beginTransaction();
            try {

                $this->_diffApply($diff);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * @param string $number
     * @return bool
     */
    private function _check7800($number)
    {
        // вместо номера 7800 мы синхронизируем ассоциированную линию
        if ($this->_is7800($number)) {
            $n = UsageVoip::find()->phone($number)->actual()->one();
            if ($n && $n->line7800_id) {
                $line = UsageVoip::findOne(['id' => $n->line7800_id]);

                if ($line) {
                    Event::go(Event::ACTUALIZE_NUMBER, ['number' => $line->E164]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $saved
     * @param array $actual
     * @return array
     */
    private function _checkDiff($saved, $actual)
    {
        $d = [];

        foreach (array_diff(array_keys($saved), array_keys($actual)) as $l) {
            $d[$l] = ['action' => 'del'] + $saved[$l];
        }

        foreach (array_diff(array_keys($actual), array_keys($saved)) as $l) {
            $d[$l] = ['action' => 'add'] + $actual[$l];
        }


        foreach ([
                     'client_id',
                     'region',
                     'call_count',
                     'number_type',
                     'number7800',
                     'is_blocked',
                     'is_disabled'
                 ] as $field) {
            foreach ($actual as $number => $l) {
                if (isset($saved[$number]) && $saved[$number][$field] != $l[$field] && !isset($d[$number])) {
                    $d[$number] = ['action' => 'update'] + $l;
                }
            }
        }

        return $d;
    }

    /**
     * @param array $diff
     */
    private function _diffToSync($diff)
    {
        foreach ($diff as $data) {
            Event::go(Event::ATS3__SYNC, $data);
        }
    }

    /**
     * @param array $saved
     * @param array $actual
     * @return array|bool
     */
    private function _diff($saved, $actual)
    {
        $d = [
            'added' => [],
            'deleted' => [],
            'changed' => [],
        ];

        foreach (array_diff(array_keys($saved), array_keys($actual)) as $l) {
            $d['deleted'][$l] = $saved[$l];
        }

        foreach (array_diff(array_keys($actual), array_keys($saved)) as $l) {
            $d['added'][$l] = $actual[$l];
        }


        foreach ($actual as $number => $l) {
            foreach ([
                         'client_id',
                         'region',
                         'call_count',
                         'number_type',
                         'number7800',
                         'is_blocked',
                         'is_disabled'
                     ] as $field) {

                if (isset($saved[$number]) && $saved[$number][$field] != $l[$field]) {
                    if (!isset($d['changed'][$number]['changed_fields'])) {
                        $d['changed'][$number]['data_new'] = $l;
                        $d['changed'][$number]['data_old'] = $saved[$number];
                    }

                    $d['changed'][$number]['changed_fields'][$field] = $l[$field];
                }
            }
        }

        foreach ($d as $k => $v) {
            if ($v) {
                return $d;
            }
        }

        return false;
    }

    /**
     * @param array $diff
     */
    private function _diffApply($diff)
    {
        if ($diff['added']) {
            $this->_applyAdd($diff['added']);
        }

        if ($diff['deleted']) {
            $this->_applyDeleted($diff['deleted']);
        }

        if ($diff['changed']) {
            $this->_applyChanged($diff['changed']);
        }
    }

    /**
     * @param array $numbers
     */
    private function _applyAdd($numbers)
    {
        foreach ($numbers as $numberData) {
            $n = new ActualNumber();
            $n->setAttributes($numberData, false);
            $n->save();

            $this->_addEvent($numberData);
        }
    }

    /**
     * @param array $numbers
     */
    private function _applyDeleted($numbers)
    {
        foreach ($numbers as $numberData) {
            ActualNumber::findOne(['number' => $numberData['number']])->delete();
            $this->_delEvent($numberData);
        }
    }

    /**
     * @param array $numbers
     */
    private function _applyChanged($numbers)
    {
        foreach ($numbers as $number => $data) {
            $n = ActualNumber::findOne(['number' => $number]);

            if ($n) {
                $n->setAttributes($data['changed_fields'], false);
                $n->save();

                $this->_changeEvent($number, $data);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function _addEvent($data)
    {
        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()->phone($data['number'])->actual()->one();
        $params = '{}';
        if ($usage) {
            $params = $usage->create_params;
        }

        $params = json_decode($params, true);
        if (!$params) {
            $params = [];
        }

        $s = [
            'client_id' => (int)$data['client_id'],
            'did' => $data['number'],
            'cl' => (int)$data['call_count'],
            'region' => (int)$data['region'],
            'timezone' => $this->_getTimezoneByRegion($data['region']),
            'type' => 'line', // $usage->type_id,
            'sip_accounts' => 1,
            'nonumber' => (bool)$this->_isNonumber($data['number'])
        ];

        if ($s['nonumber'] && $data['number7800']) {
            $s['nonumber_phone'] = $data['number7800'];
        }

        if (isset($params['vpbx_stat_product_id'])) {
            $s['vpbx_stat_product_id'] = $params['vpbx_stat_product_id'];
            $s['type'] = 'vpbx';
        }

        $this->_execQuery('add_did', $s);

        return $s;
    }

    /**
     * @param array $data
     */
    private function _delEvent($data)
    {
        $s = [
            'client_id' => $data['client_id'],
            'did' => $data['number']
        ];

        $this->_execQuery('disable_did', $s);
    }

    /**
     * @param string $number
     * @param array $data
     */
    private function _changeEvent($number, $data)
    {
        $old = $data['data_old'];
        $new = $data['data_new'];
        $changedFields = $data['changed_fields'];

        $structClientChange = null;

        // change client_id
        if (isset($changedFields['client_id'])) {

            $isMoved = false;
            $usage = UsageVoip::find()->phone($number)->actual()->one();
            if ($usage) {
                $isMoved = $usage->prev_usage_id;
            }

            if ($isMoved) {
                $structClientChange = [
                    'old_client_id' => (int)$old['client_id'],
                    'did' => $number,
                    'new_client_id' => (int)$new['client_id']
                ];

                $this->_execQuery('edit_client_id', $structClientChange);
                unset($changedFields['client_id']);
            } else {
                $this->_delEvent($old);
                $this->_addEvent($new);
                return true;
            }
        }

        // номер заблокирован (есть только входящая связь)
        if (isset($changedFields['is_blocked'])) {

            if ($new['is_blocked']) {
                Event::go(Event::ATS3__BLOCKED, $new);
            } else {
                Event::go(Event::ATS3__UNBLOCKED, $new);
            }

            unset($changedFields['is_blocked']);
        }

        // номер временно отключен (отключение и входящей и исходящей связи)
        if (isset($changedFields['is_disabled'])) {
            $s = [
                'client_id' => (int)$new['client_id'],
                'number' => $number
            ];

            Event::go(Event::ATS3__DISABLED_NUMBER, $s);

            unset($changedFields['is_disabled']);
        }

        // change fields
        if ($changedFields) {
            $structChange = [
                'client_id' => (int)$new['client_id'],
                'did' => $number,
                'cl' => (int)$new['call_count']
            ];

            if (isset($changedFields['region'])) {
                $structChange['region'] = (int)$changedFields['region'];
                $structChange['timezone'] = $this->_getTimezoneByRegion($changedFields['region']);
            }

            if (isset($changedFields['number7800'])) {
                $structChange['nonumber_phone'] = $changedFields['number7800'];
            }

            $this->_execQuery('edit_did', $structChange);
        }
    }

    /**
     * @param string $number
     * @return bool
     */
    private function _isNonumber($number)
    {
        return (strlen($number) < 6);
    }

    /**
     * @param string $number
     * @return bool
     */
    private function _is7800($number)
    {
        return (strpos($number, '7800') === 0);
    }

    /**
     * @param int $regionId
     * @return string
     */
    private function _getTimezoneByRegion($regionId)
    {
        $region = Region::findOne($regionId);

        if ($region) {
            return $region->timezone_name;
        }

        return 'Europe/Moscow';
    }

    /**
     * @param string $number
     * @param int $toClientId
     */
    public static function transferNumberWithVpbx($number, $toClientId)
    {
        $actual = ActualNumber::findOne(['number' => $number]);
        if ($actual) {
            $actual->client_id = $toClientId;
            $actual->save();
        }
    }


    /**
     * @param string $action
     * @param array $data
     */
    private function _execQuery($action, $data)
    {
        if (!defined('ats3_silent')) {
            ApiPhone::me()->exec($action, $data);
        }
    }
}

