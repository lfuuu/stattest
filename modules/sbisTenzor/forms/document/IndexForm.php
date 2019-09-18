<?php

namespace app\modules\sbisTenzor\forms\document;

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
     * Получить зпрос на выборку списка документов
     *
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = SBISDocument::find();

        if ($this->state) {
            $query->where(['=', 'state', $this->state]);
        } else {
            $query
                ->where(['>=', 'state', SBISDocumentStatus::CREATED])
                ->andWhere(['!=', 'state', SBISDocumentStatus::ACCEPTED]);
        }

        if ($this->client) {
            $query->andWhere(['client_account_id' => $this->client->id]);
        }

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
}