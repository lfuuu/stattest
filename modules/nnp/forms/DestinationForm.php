<?php

namespace app\modules\nnp\forms;

use app\classes\Form;
use app\classes\uu\forms\CrudMultipleTrait;
use app\modules\nnp\models\Destination;
use app\modules\nnp\models\PrefixDestination;
use InvalidArgumentException;
use yii;

abstract class DestinationForm extends Form
{
    use CrudMultipleTrait;

    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Destination */
    public $destination;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Destination
     */
    abstract public function getDestinationModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->destination = $this->getDestinationModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $db = Destination::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->destination->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->destination->load($post)) {

                // создать/редактировать
                if ($this->destination->validate() && $this->destination->save()) {
                    $this->id = $this->destination->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->destination->getFirstErrors();
                }

                // префиксы (+/-)
                $prefixDestination = new PrefixDestination();
                $prefixDestination->destination_id = $this->id;

                // префиксы (+)
                $prefixDestination->is_addition = true;
                $additionPrefixDestinations = self::crudMultipleSelect2($this->destination->additionPrefixDestinations, $post, $prefixDestination, $fieldName = 'prefix_id', $formName = 'AdditionPrefixDestination');

                // префиксы (-)
                $prefixDestination->is_addition = false;
                $subtractionPrefixDestinations = self::crudMultipleSelect2($this->destination->subtractionPrefixDestinations, $post, $prefixDestination, $fieldName = 'prefix_id', $formName = 'SubtractionPrefixDestination');

                $prefixDestinationsIntersect = array_intersect_key($additionPrefixDestinations, $subtractionPrefixDestinations);
                if (count($prefixDestinationsIntersect)) {
                    /** @var PrefixDestination $prefixDestination */
                    $prefixDestination = reset($prefixDestinationsIntersect);
                    throw new InvalidArgumentException('Нет смысла один и тот же префикс добавлять одновременно в (+) и (-): ' . $prefixDestination->prefix->name);
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;
            $this->validateErrors[] = $e->getMessage();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }
}
