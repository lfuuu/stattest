<?php

namespace app\classes;

use app\classes\api\ApiCore;
use app\models\ProductState;
use Exception;
use yii\base\InvalidConfigException;
use app\classes\api\ApiFeedback;
use app\dao\ActualCallChatDao;
use app\models\ActualCallChat;
use app\models\UsageCallChat;


class ActaulizerCallChatUsage extends Singleton
{

    public function actualizeUsages()
    {
        if (($diff = $this->checkDiff(
            ActualCallChatDao::me()->loadSaved(),
            ActualCallChatDao::me()->collectFromUsages()
        ))
        ) {
            $this->makeEventFromDiff($diff);
        }
    }

    public function actualizeUsage($usageId)
    {
        $diff = $this->checkDiff(
            ActualCallChatDao::me()->loadSaved($usageId),
            ActualCallChatDao::me()->collectFromUsages($usageId)
        );

        if ($diff) {
            $this->applyDiff($diff);
        }
    }

    private function checkDiff($saved, $actual)
    {
        $diff = [];

        foreach (array_diff(array_keys($actual), array_keys($saved)) as $l) {
            if (!isset($diff['add'])) {
                $diff['add'] = [];
            }
            $diff['add'][] = $actual[$l];
        }


        foreach (array_diff(array_keys($saved), array_keys($actual)) as $l) {
            if (!isset($diff['del'])) {
                $diff['del'] = [];
            }
            $diff['del'][] = $saved[$l];
        }


        return $diff;
    }

    private function makeEventFromDiff($diff)
    {
        if (isset($diff['add'])) {
            foreach ($diff['add'] as $row) {
                Event::go('call_chat__add', $row);
            }
        }

        if (isset($diff['del'])) {
            foreach ($diff['del'] as $row) {
                Event::go('call_chat__del', $row);
            }
        }
    }

    private function applyDiff($diff)
    {
        if (isset($diff['add'])) {
            foreach ($diff['add'] as $row) {
                $this->applyAdd($row);
            }
        }

        if (isset($diff['del'])) {
            foreach ($diff['del'] as $row) {
                $this->applyDel($row);
            }
        }
    }

    private function applyAdd($row)
    {
        $transaction = ActualCallChat::getDb()->beginTransaction();
        try {

            $callChatRow = new ActualCallChat();
            $callChatRow->client_id = $row['client_id'];
            $callChatRow->usage_id = $row['usage_id'];
            $callChatRow->tarif_id = $row['tarif_id'];
            $callChatRow->save();

            $this->sendAddEvent($callChatRow);
            $this->checkProductToAdd($callChatRow);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    private function applyDel($row)
    {
        $transaction = ActualCallChat::getDb()->beginTransaction();

        try {
            $callChatRow = ActualCallChat::findOne([
                'client_id' => $row['client_id'],
                'usage_id' => $row['usage_id']
            ]);

            if ($this->sendDelEvent($callChatRow)) {
                $callChatRow->delete();
            }

            $this->checkProductToDel($callChatRow);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    private function sendAddEvent(ActualCallChat $callChatRow)
    {
        if ($usage = UsageCallChat::findOne(['id' => $callChatRow->usage_id])) {
            try {
                return ApiFeedback::createChat($callChatRow->client_id, $usage->id, $usage->comment);
            } catch (InvalidConfigException $e) {
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        }
        return true;
    }

    private function sendDelEvent(ActualCallChat $callChatRow)
    {
        try {
            return ApiFeedback::removeChat($callChatRow->client_id, $callChatRow->usage_id);
        } catch (InvalidConfigException $e) {
            return true;
        } catch (Exception $e) {
            throw $e;
        }

    }

    private function checkProductToAdd(ActualCallChat $callChatRow)
    {
        $usage = ProductState::findOne([
            'client_id' => $callChatRow->client_id,
            'product' => ProductState::FEEDBACK
        ]);

        if (!$usage) {

            try {
                ApiCore::addProduct('feedback', $callChatRow->client_id);
                $state = new ProductState;
                $state->client_id = $callChatRow->client_id;
                $state->product = ProductState::FEEDBACK;
                $state->save();

            } catch (InvalidConfigException $e) {
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        }
        return true;
    }

    private function checkProductToDel(ActualCallChat $callChatRow)
    {
        if ($usage = ProductState::findOne([
            'client_id' => $callChatRow->client_id,
            'product' => ProductState::FEEDBACK
        ])
        ) {

            try {
                ApiCore::remoteProduct('feedback', $callChatRow->client_id);
                $usage->delete();
            } catch (InvalidConfigException $e) {
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        }
        return true;
    }
}

