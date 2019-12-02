<?php

namespace app\modules\sbisTenzor\forms\document;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\models\SBISDocument;
use yii\data\ActiveDataProvider;

class IndexForm extends \app\classes\Form
{
    /** @var ClientAccount */
    protected $client;

    protected $state;

    /**
     * Index form constructor
     *
     * @param int $state
     * @param ClientAccount|null $client
     */
    public function __construct($state, ClientAccount $client = null)
    {
        parent::__construct();

        $this->state = $state;
        $this->client = $client;
    }

    /**
     * Получить запрос на выборку списка документов
     *
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = SBISDocument::find();

        if ($this->state) {
            $states = [$this->state];
            if ($this->state == SBISDocumentStatus::CREATED) {
                $states[] = SBISDocumentStatus::CREATED_AUTO;
            }
            if ($this->state == SBISDocumentStatus::CANCELLED) {
                $states[] = SBISDocumentStatus::CANCELLED_AUTO;
            }

            $query->where(['state' => $states]);
        } else {
            $query
                ->where(['>=', 'state', SBISDocumentStatus::CREATED])
                ->andWhere(['!=', 'state', SBISDocumentStatus::ACCEPTED])
                ->andWhere(['!=', 'state', SBISDocumentStatus::ERROR]);
        }

        if ($this->client) {
            $query->andWhere(['client_account_id' => $this->client->id]);
        }

        $query->orderBy([
            'updated_at' => SORT_DESC,
            'id' => SORT_DESC,
        ]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return
            $this->client ?
                sprintf('Пакеты документов в СБИС для клиента %s', $this->client->contragent->name) :
                'Пакеты документов в СБИС'
            ;
    }

    /**
     * @return string
     */
    public function getSendAutoConfirmText()
    {
        return
            $this->client ?
                sprintf('Отправить подготовленные пакеты для клиента %s?', $this->client->contragent->name) :
                'Отправить подготовленные пакеты по всем клиентам?'
            ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    protected function getSendAutoQuery()
    {
        $query = SBISDocument::find();

        $query
            ->where(['=', 'state', SBISDocumentStatus::CREATED_AUTO]);

        if ($this->client) {
            $query->andWhere(['client_account_id' => $this->client->id]);
        }

        return $query;
    }

    /**
     * Возвращает количество подготовленных пакетов докуметов
     *
     * @return int
     */
    public function getSendAutoCount()
    {
        return $this->getSendAutoQuery()->count();
    }

    /**
     * Отправляет все подготовленные пакеты документов
     *
     * @throws \Exception
     */
    public function sendAuto()
    {
        $query = $this->getSendAutoQuery();

        /** @var SBISDocument $document */
        foreach ($query->each() as $document) {
            $document->setState(SBISDocumentStatus::PROCESSING);
            if (!$document->save()) {
                throw new ModelValidationException($document);
            }
        }
    }
}