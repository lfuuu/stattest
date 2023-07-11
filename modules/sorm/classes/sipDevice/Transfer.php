<?php

namespace app\modules\sorm\classes\sipDevice;

use app\exceptions\ModelValidationException;
use app\modules\sorm\models\SipDevice\State;

class Transfer extends \app\classes\Singleton
{
    private array $stat = [];

    public function go()
    {
        $this->loadFromStat();

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var State $row */
            foreach (self::loadFromLk() as $row) {
                $key = $row->getStateKey();
                if (isset($this->stat[$key])) {
                    unset($this->stat[$key]);
                    continue;
                }
                echo PHP_EOL . date('r') . ': (+) ' . $key;
                if (!$row->save()) {
                    throw new ModelValidationException($row);
                }
            }

            if ($this->stat) {
                /** @var State $state */
                foreach ($this->stat as $state) {
                    $key = $state->getStateKey();
                    echo PHP_EOL . date('r') . ': (-) ' . $key;
                    $state->delete();
                }
            }

            $transaction->commit();
            echo PHP_EOL . date('r') . ': OK';
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function loadFromLk(): \Iterator
    {
        $query = \Yii::$app->dbPg->createCommand('
select account_id,
       region_id,
       did,
       ndc_type_id,
       server as sip_login,
       created_at
from sorm_itgrad.get_sipdevices()
')->query();

        while ($row = $query->read()) {
            $state = new State();
            $state->load($row, '');
            $state->fixLoad();

            yield $state;
        }
    }

    private function loadFromStat()
    {
        $this->stat = State::find()
            ->indexBy(fn(State $state) => $state->getStateKey())
            ->all();
    }
}