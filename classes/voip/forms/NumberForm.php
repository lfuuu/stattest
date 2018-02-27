<?php

namespace app\classes\voip\forms;

use app\classes\Form;
use InvalidArgumentException;
use yii;

abstract class NumberForm extends Form
{
    /** @var \app\models\Number */
    public $number;

    /**
     * @return \app\models\Number
     */
    abstract public function getNumberModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->number = $this->getNumberModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->number->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->number->load($post)) {

                // создать/редактировать
                if ($this->number->validate() && $this->number->save()) {
                    $this->id = $this->number->number;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->number->getFirstErrors();
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
