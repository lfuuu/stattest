<?php

namespace app\modules\uu\forms;

use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\models\rewards\RewardClientContractService;
use app\models\rewards\RewardsServiceTypeActive;
use app\modules\uu\models\ServiceType;
use app\models\rewards\RewardsServiceTypeResource;
use app\modules\uu\models\ResourceModel;
use InvalidArgumentException;
use LogicException;

abstract class ServiceTypeForm extends Form
{
    /** @var ServiceType */
    public $serviceType = false;

    /** @var RewardsServiceTypeResource[] */
    public $serviceTypeResources = false;

     /** @var RewardsServiceTypeActive */
     public $serviceTypeActive = false;

    /**
     * @return ServiceType
     */
    abstract public function getServiceTypeModel();

    /**
     * @return RewardsServiceTypeResource[]
     */
    abstract public function getServiceTypeResources();

    /**
     * @return RewardsServiceTypeActive
     */
    abstract public function getServiceTypeActive();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->serviceType = $this->getServiceTypeModel();
        $this->serviceTypeResources = $this->createResources($this->getServiceTypeResources());
        $this->serviceTypeActive = $this->createServiceTypeActive($this->getServiceTypeActive());

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        $post = \Yii::$app->request->post();
        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (isset($post['dropButton'])) {

                // удалить
                $this->serviceType->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->serviceType->load($post) && $this->serviceTypeActive->load($post)) {

                if (!$this->serviceType->save()) {
                    $this->validateErrors += $this->serviceType->getFirstErrors();
                    throw new ModelValidationException($this->serviceType);
                }

                if ($post['RewardsServiceTypeActive']['is_active']) {
                    $this->serviceTypeActive->is_active = $post['RewardsServiceTypeActive']['is_active'];
                    if (!$this->serviceTypeActive->save()) {
                        $this->validateErrors += $this->serviceTypeActive->getFirstErrors();
                        throw new ModelValidationException($this->serviceTypeActive);
                    }

                    if (RewardsServiceTypeResource::loadMultiple($this->serviceTypeResources, $post)) {
                        foreach ($this->serviceTypeResources as $index => $model) {
                            if ($post['RewardsServiceTypeResource'][$index]['is_active'] > 0 ) {
                                $model->is_active = $post['RewardsServiceTypeResource'][$index]['is_active'] ;

                                if (!$model->save()) {
                                    throw new ModelValidationException($model);
                                }

                            } else {
                                if ($model['id'] != null) {
                                    if (!$model->delete()) {
                                        throw new ModelValidationException($model);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (RewardClientContractService::findOne(['service_type_id' => $this->serviceType->id])) {
                        throw new LogicException('Невозможно отключить вознаграждение, данный сервис используется партнером');
                    }

                    if (!$this->serviceTypeActive->delete()) {
                        throw new ModelValidationException($this->serviceTypeActive);
                    }

                    if ($this->serviceTypeResources) {
                        foreach ($this->serviceTypeResources as $model) {
                            if ($model['id'] != null) {
                                if (!$model->delete()) {
                                    throw new ModelValidationException($model);
                                }
                            }
                        }
                    }
                    
                }

                $this->id = $this->serviceType->id;
                $this->isSaved = true;

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
            \Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : \Yii::t('common', 'Internal error');
        }
    }

    public function createServiceTypeActive($model)
    {   
        if (!$model) {
            $model = new RewardsServiceTypeActive();
            $model->service_type_id = $this->id;
        }

        return $model;
    }

    public function createResources($serviceTypeResources)
    {
        $resources = ResourceModel::getList($this->id);
        $serviceResources = [];
        foreach ($resources as $id => $resource) {
            $isThere = false;
            foreach ($serviceTypeResources as $serviceResource) {
                if ($serviceResource['resource_id'] == $id){
                    $isThere = true;
                    $serviceResources[] = $serviceResource;
                }
            } 

            if (!$isThere) {
                $newResource = new RewardsServiceTypeResource();
                $newResource->service_type_id = $this->id;
                $newResource->resource_id = $resource['id'];
                $serviceResources[] = $newResource;
            } 
        }

        return $serviceResources;
    }

    
}
