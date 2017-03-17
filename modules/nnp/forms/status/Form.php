<?php

namespace app\modules\nnp\forms\status;

use app\modules\nnp\models\Status;
use InvalidArgumentException;
use Yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Status */
    public $status;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Status
     */
    abstract public function getStatusModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->status = $this->getStatusModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Status::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->status->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->status->load($post)) {

                // создать/редактировать
                if ($this->status->validate() && $this->status->save()) {
                    $this->id = $this->status->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->status->getFirstErrors();
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
