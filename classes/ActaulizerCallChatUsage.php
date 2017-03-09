<?php

namespace app\classes;

use app\classes\api\ApiCore;
use app\exceptions\ModelValidationException;
use app\models\ProductState;
use Exception;
use yii\base\InvalidConfigException;
use app\classes\api\ApiFeedback;
use app\dao\ActualCallChatDao;
use app\models\ActualCallChat;
use app\models\UsageCallChat;


class ActaulizerCallChatUsage extends Singleton
{

    /**
     * Проверка и генерация событий на изменения
     */
    public function actualizeUsages()
    {
        if (($diff = $this->_checkDiff(
            ActualCallChatDao::me()->loadSaved(),
            ActualCallChatDao::me()->collectFromUsages()
        ))
        ) {
            $this->_makeEventFromDiff($diff);
        }
    }

    /**
     * Актализация отдельной услуги
     *
     * @param int $usageId
     */
    public function actualizeUsage($usageId)
    {
        $diff = $this->_checkDiff(
            ActualCallChatDao::me()->loadSaved($usageId),
            ActualCallChatDao::me()->collectFromUsages($usageId)
        );

        if ($diff) {
            $this->_applyDiff($diff);
        }
    }

    /**
     * Поиск изменений между актуальными услугами и сохраненными на платформе
     *
     * @param array $saved
     * @param array $actual
     * @return array
     */
    private function _checkDiff($saved, $actual)
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

        foreach ($actual as $usageId => $usage) {
            if (isset($saved[$usageId])) {
                if ($saved[$usageId]['tarif_id'] != $usage['tarif_id']) {
                    $diff['change'][] = [
                        'usage_id' => $usageId,
                        'client_id' => $usage['client_id'],
                        'tarif_id' => $usage['tarif_id'],
                    ];
                }
            }
        }

        return $diff;
    }

    /**
     * Генерация событий на изменения на основе diff'а
     *
     * @param array $diff
     */
    private function _makeEventFromDiff($diff)
    {
        if (isset($diff['add'])) {
            foreach ($diff['add'] as $row) {
                Event::go(Event::CALL_CHAT__ADD, $row);
            }
        }

        if (isset($diff['change'])) {
            foreach ($diff['change'] as $row) {
                Event::go(Event::CALL_CHAT__UPDATE, $row);
            }
        }

        if (isset($diff['del'])) {
            foreach ($diff['del'] as $row) {
                Event::go(Event::CALL_CHAT__DEL, $row);
            }
        }
    }

    /**
     * Применение изменений на основе diff'а
     *
     * @param array $diff
     */
    private function _applyDiff($diff)
    {
        if (isset($diff['add'])) {
            foreach ($diff['add'] as $row) {
                $this->_applyAdd($row);
            }
        }

        if (isset($diff['change'])) {
            foreach ($diff['change'] as $row) {
                $this->_applyUpdate($row);
            }
        }

        if (isset($diff['del'])) {
            foreach ($diff['del'] as $row) {
                $this->_applyDel($row);
            }
        }
    }

    /**
     * Добавление новой услуги
     *
     * @param array $row
     * @throws Exception
     */
    private function _applyAdd($row)
    {
        $transaction = ActualCallChat::getDb()->beginTransaction();
        try {
            $callChatRow = new ActualCallChat();
            $callChatRow->client_id = $row['client_id'];
            $callChatRow->usage_id = $row['usage_id'];
            $callChatRow->tarif_id = $row['tarif_id'];
            if (!$callChatRow->save()) {
                throw new ModelValidationException($callChatRow);
            }

            $this->_sendAddEvent($callChatRow);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Обновление услуги. В данный момент только тариф.
     *
     * @param array $row
     * @throws Exception
     */
    private function _applyUpdate($row)
    {
        $transaction = ActualCallChat::getDb()->beginTransaction();
        try {
            $callChatRow = ActualCallChat::findOne([
                'usage_id' => $row['usage_id'],
                'client_id' => $row['client_id'],
            ]);

            if (!is_null($callChatRow)) {
                $callChatRow->tarif_id = $row['tarif_id'];
                if (!$callChatRow->save()) {
                    throw new ModelValidationException($callChatRow);
                }

                $this->_sendUpdateEvent($callChatRow);
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Удаление услуги
     *
     * @param array $row
     * @throws Exception
     */
    private function _applyDel($row)
    {
        $transaction = ActualCallChat::getDb()->beginTransaction();

        try {
            $callChatRow = ActualCallChat::findOne([
                'client_id' => $row['client_id'],
                'usage_id' => $row['usage_id']
            ]);

            $this->_sendDelEvent($callChatRow);
            $callChatRow->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Отправка комманды на создание услуги
     *
     * @param ActualCallChat $callChatRow
     * @return bool|mixed
     * @throws Exception
     */
    private function _sendAddEvent(ActualCallChat $callChatRow)
    {
        if ($usage = UsageCallChat::findOne(['id' => $callChatRow->usage_id])) {
            try {
                return ApiFeedback::createChat($callChatRow->client_id, $usage->id);
            } catch (InvalidConfigException $e) {
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Отправка комманды на обновление услуги
     *
     * @param ActualCallChat $callChatRow
     * @return bool|mixed
     * @throws Exception
     */
    private function _sendUpdateEvent(ActualCallChat $callChatRow)
    {
        /**
         * В данный момент разные тарифы не поддерживаются продуктом.
         * Ранее применялась для синхронизации названия чата. Названия изменяется внутри самого продукта.
         * На данный момент функция API ждет названия, которого нет.
         * Будет включена в будущем.
         */

        return true;

        if ($usage = UsageCallChat::findOne(['id' => $callChatRow->usage_id])) {
            try {
                return ApiFeedback::updateChat($callChatRow->client_id, $usage->id);
            } catch (InvalidConfigException $e) {
                return true;
            } catch (Exception $e) {
                throw $e;
            }
        }
        return true;
    }

    /**
     * Отправка комманды на удаление услуги
     *
     * @param ActualCallChat $callChatRow
     * @return bool|mixed
     * @throws Exception
     */
    private function _sendDelEvent(ActualCallChat $callChatRow)
    {
        try {
            return ApiFeedback::removeChat($callChatRow->client_id, $callChatRow->usage_id);
        } catch (InvalidConfigException $e) {
            return true;
        } catch (Exception $e) {
            throw $e;
        }

    }
}
