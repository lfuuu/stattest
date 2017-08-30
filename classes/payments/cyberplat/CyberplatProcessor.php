<?php

namespace app\classes\payments\cyberplat;

use app\classes\payments\cyberplat\exceptions\AnswerErrorAction;
use app\classes\payments\cyberplat\exceptions\AnswerErrorCancel;
use app\classes\payments\cyberplat\exceptions\AnswerOk;
use app\classes\payments\cyberplat\exceptions\CyberplatError;
use app\classes\payments\cyberplat\exceptions\CyberplatOk;

class CyberplatProcessor
{
    // request fields
    private $_data = array();

    /** @var CyberplatActionCheck */
    private $_actionChecker = null;

    private $_isNeedCheckSign = true;

    private $_answer = "";
    private $_answerCode = null;
    private $_answerData = [];

    /**
     * Основная функция запуска обработчика
     *
     * @param string $action
     * @return $this
     */
    public function proccessRequest($action = null)
    {
        if (!$action) {
            $action = \Yii::$app->request->get('action') ?: \Yii::$app->request->post('action');
        }

        $this->_answer = "";

        try {
            $result = $this->_doAction($action);
            $this->_answer = $this->_makeOkAnswer($result);
        } catch (CyberplatError $e) {
            $this->_answer = $this->_makeErrorAnswer($e);
        } catch (\Exception $e) {
            $this->_log("-------------------------");
            $this->_log("unexcepted error");
            $this->_log($e->getMessage());

            $ee = new CyberplatError("Внутренная ошибка");
            $this->_answer = $this->_makeErrorAnswer($ee);
        }

        return $this;
    }

    /**
     * Запуск обработки действия
     *
     * @param string $action
     * @throws AnswerErrorAction
     */
    private function _doAction($action)
    {
        $this->_load();
        $this->_log($this->_data);

        $this->_actionChecker = new CyberplatActionCheck();

        if ($this->_isNeedCheckSign) {
            $this->_actionChecker->assertSign();
        }

        switch ($action) {
            case 'check':
                return $this->_actionCheck();
                break;
            case 'payment':
                return $this->_actionPayment();
                break;
            case 'status':
                return $this->_actionStatus();
                break;
            case 'cancel':
                return $this->_actionCancel();
                break;
            default:
                throw new AnswerErrorAction();
        }
    }

    /**
     * Установить данные
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * Не проверять подпись (нужно для тестов)
     */
    public function setNoCheckSign()
    {
        $this->_isNeedCheckSign = false;
    }

    /**
     * Действие "проверка платежа"
     *
     * @throws AnswerOk
     */
    private function _actionCheck()
    {
        return $this->_actionChecker->check($this->_data);
    }

    /**
     * Действие "проведение платежа"
     */
    private function _actionPayment()
    {
        return $this->_actionChecker->payment($this->_data);
    }

    /**
     * Действие "статус платежа"
     */
    private function _actionStatus()
    {
        return $this->_actionChecker->status($this->_data);
    }

    /**
     * Действие "отмена платежа"
     *
     * @throws AnswerErrorCancel
     */
    private function _actionCancel()
    {
        throw new AnswerErrorCancel();
    }

    /**
     * Загрузка входных данных
     *
     * @return bool
     */
    private function _load()
    {
        if ($this->_data) {
            return true;
        }

        foreach (["number", "amount", "type", "sign", "receipt", "date", "mes", "additional"] as $field) {
            $this->_data[$field] = \Yii::$app->request->get($field) ?: \Yii::$app->request->post($field);
        }

        return true;
    }

    /**
     * Логирование
     *
     * @param string|array $data
     */
    private function _log($data)
    {
        \Yii::info('Cyberplat: ' . var_export($data, true));
    }

    /**
     * Ответ. Ошибка.
     *
     * @param CyberplatError $e
     * @return string
     */
    private function _makeErrorAnswer(CyberplatError $e)
    {
        $str = '<?xml version="1.0" encoding="windows-1251"?>' .
            '<response>' .
            '<code>' . $e->getCode() . '</code>' .
            '<message>' . $e->getMessage() . '</message>' .
            '</response>';

        $this->_log($str);

        $this->_answerCode = $e->getCode();
        $this->_answerData = [];

        return CyberplatCrypt::me()->sign($str);
    }

    /**
     * Ответ. OK.
     *
     * @param CyberplatOk $e
     * @return string
     */
    private function _makeOkAnswer(CyberplatOk $e)
    {
        $str = '<?xml version="1.0" encoding="windows-1251"?>' .
            '<response>' .
            '<code>0</code>\n' . $e->getDataStr() .
            '<message>' . $e->getMessage() . '</message>' .
            '</response>';
        $this->_log($str);

        $this->_answerCode = 0;
        $this->_answerData = $e->data;

        return CyberplatCrypt::me()->sign($str);
    }

    /**
     * Выдать ответ
     *
     * @return string
     */
    public function echoAnswer()
    {
        header('Content-Type:text/html; charset=windows-1251');

        echo iconv('utf-8', 'windows-1251//TRANSLIT', $this->_answer);
    }

    /**
     * Получить ответ
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->_answer;
    }

    /**
     * Код ответа
     *
     * @return integer
     */
    public function getAnswerCode()
    {
        return $this->_answerCode;
    }

    /**
     * Данные ответа
     *
     * @return array
     */
    public function getAnswerData()
    {
        return $this->_answerData;
    }
}
