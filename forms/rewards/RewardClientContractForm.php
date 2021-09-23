<?php

namespace app\forms\rewards;

use ActiveRecord\ModelException;
use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;

use app\models\rewards\RewardClientContractService;
use app\models\rewards\RewardClientContractResource;
use app\models\rewards\RewardsServiceTypeActive;
use DateTime;
use Exception;
use InvalidArgumentException;
use LogicException;
use yii;
use yii\base\UserException;

abstract class RewardClientContractForm extends Form
{

    /** @var RewardClientContractService[] */
    public $serviceRewards;

    /** @var RewardClientContractResource[] */
    public $resourceRewards;

    /**
     * @return RewardClientContractService[]
     */
    abstract public function getRewardClientContractServices();

    /**
     * @return RewardClientContractResource[]
     */
    abstract public function getRewardClientContractResources();

    /**
     * Конструктор
     */
    public function init()
    {
        global $fixclient;
        $client = ClientAccount::find()->where(['id' => $fixclient])->asArray()->one();
        $this->id = $client['contract_id'];
        $this->serviceRewards = $this->createServiceTypesInputFields($this->getRewardClientContractServices());
        $this->resourceRewards = $this->createResourcesFromServiceTypes($this->serviceRewards, $this->getRewardClientContractResources());
        // Обработать submit (создать, редактировать)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать)
     */
    protected function loadFromInput()
    {
        $transaction = \Yii::$app->db->beginTransaction();     
        try {
            $post = Yii::$app->request->post();
            if (
                RewardClientContractService::loadMultiple($this->serviceRewards, $post)
            ) {
                $this->addValidRewardServices($post);
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
            Yii::error($e);
            $this->isSaved = false;
            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }

    private function createServiceTypesInputFields($serviceRewards)
    {
        $activeServices = RewardsServiceTypeActive::findAll(['is_active' => RewardsServiceTypeActive::ACTIVE]);
        if (!$activeServices) {
            throw new UserException('Нету активных сервисов, пожалуйста включите.');
        }
        $services = [];
        foreach ($activeServices as $serviceTypeActive) {
            $newReward = new RewardClientContractService();
            $newReward->service_type_id = $serviceTypeActive->service_type_id;
            $services[] = $newReward;
        }
        return $services;
    }

    private function createResourcesFromServiceTypes($serviceRewards, $resources)
    {
        $resources = [];
        foreach ($serviceRewards as $reward) {
            $activeResources = $reward->activeResources;
            foreach ($activeResources as $resource) {
                $newResource = new RewardClientContractResource();
                $newResource->service_type_id = $reward->service_type_id;
                $newResource->resource_id = $resource->resource_id;
                $resources[$reward->service_type_id][] = $newResource;
            }
        }
        return $resources;
    }

    //сохраняем заполненые поля и удаляем пустые
    private function addValidRewardServices($post)
    {
        foreach ($this->serviceRewards as $index => $model) {
            if ($model['actual_from'] != null) {
                $now = (new DateTime('first day of this month'))->format("Y-m-d");
                $actualFrom = (new DateTime($model->actual_from . '-01'))->format("Y-m-d");

                if ($actualFrom <= $now) {
                    throw new LogicException('Вознаграждениями можно управлять только со следующего месяца.');
                }

                $currentModel = RewardClientContractService::find()
                    ->where([
                        'actual_from' => $actualFrom,
                        'service_type_id' => $model->service_type_id,
                        'client_contract_id' => $this->id
                    ])
                    ->one();

                if ($currentModel) {
                    $model->id = $currentModel->id;
                    $currentModel->attributes = $model->attributes;
                    $model = $currentModel;
                }

                $model->insert_time = (new DateTime())->format('Y-m-d H:m:s');
                $model->user_id = Yii::$app->user->id;
                $model->actual_from = $actualFrom;
                $model->client_contract_id = $this->id;

                if (!$model->save()) {
                    throw new ModelException($model);
                }

                if (RewardClientContractResource::validateMultiple($this->resourceRewards[$model->service_type_id], $post)) {
                    foreach ($this->resourceRewards[$model->service_type_id] as $id => $resource) {
                        $resource['price_percent'] = (int) $post['RewardClientContractResource'][$index][$id]['price_percent'];
                        $resource['percent_margin_fee'] = (int) $post['RewardClientContractResource'][$index][$id]['percent_margin_fee'];
                        $currentResource = RewardClientContractResource::find()
                            ->where([
                                'reward_service_id' => $model->id,
                                'resource_id' => $resource->resource_id
                            ])
                            ->one();

                        if ($resource['price_percent']) {
                            if ($currentResource) {
                                $resource->id = $currentResource->id;
                                $currentResource->attributes = $resource->attributes;
                                $resource = $currentResource;
                            }

                            $resource->reward_service_id = $model->id;
                            if (!$resource->save()) {
                                throw new ModelException($resource);
                            }
                        } elseif (!$resource['price_percent'] && $currentResource) {
                            if (!$currentResource->delete()) {
                                throw new ModelException($currentResource);
                            }
                        }
                    }
                }
            }
        }
    }
}
