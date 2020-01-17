<?php

namespace app\modules\nnp\forms\destination;

use app\modules\nnp\models\Destination;
use app\modules\nnp\models\DestinationMajor;
use app\modules\nnp\models\PrefixDestination;
use InvalidArgumentException;
use yii;
use yii\web\NotFoundHttpException;

abstract class Form extends \app\classes\Form
{

    /** @var Destination */
    public $destination;

    /**
     * @return Destination
     */
    abstract public function getDestinationModel();

    /**
     * Конструктор
     *
     * @throws NotFoundHttpException
     * @throws yii\db\Exception
     */
    public function init()
    {
        $this->destination = $this->getDestinationModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     *
     * @throws NotFoundHttpException
     * @throws yii\db\Exception
     */
    protected function loadFromInput()
    {
        if (!$this->destination) {
            throw new NotFoundHttpException('Объект с таким ID не существует');
        }

        // загрузить параметры от юзера
        $db = Destination::getDb();
        $transaction = $db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

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
                $additionPrefixDestinations = $this->crudMultipleSelect2($this->destination->additionPrefixDestinations, $post, $prefixDestination, $fieldName = 'prefix_id', $formName = 'AdditionPrefixDestination');

                // префиксы (-)
                $prefixDestination->is_addition = false;
                $subtractionPrefixDestinations = $this->crudMultipleSelect2($this->destination->subtractionPrefixDestinations, $post, $prefixDestination, $fieldName = 'prefix_id', $formName = 'SubtractionPrefixDestination');

                $destinationMajor = new DestinationMajor();
                $destinationMajor->destination_id = $this->id;
                $this->crudMultipleSelect2($this->destination->destinationMajors, $post, $destinationMajor, $fieldName = 'major_id', $formName = 'DestinationMajor');

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

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
