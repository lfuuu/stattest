<?php

namespace app\modules\nnp\forms\status;

use app\modules\nnp\models\Status;
use InvalidArgumentException;
use Yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{
    /** @var Status */
    public $status;

    /**
     * @return Status
     */
    abstract public function getStatusModel();

    /**
     * Конструктор
     *
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->status = $this->getStatusModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     *
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    protected function loadFromInput()
    {
        if (!$this->status) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

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
