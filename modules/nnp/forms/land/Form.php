<?php

namespace app\modules\nnp\forms\land;

use app\modules\nnp\models\Land;
use InvalidArgumentException;
use Yii;

abstract class Form extends \app\classes\Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Land */
    public $land;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Land
     */
    abstract public function getLandModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->land = $this->getLandModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Land::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->land->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->land->load($post)) {

                // создать/редактировать
                if ($this->land->validate() && $this->land->save()) {
                    $this->id = $this->land->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->land->getFirstErrors();
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
