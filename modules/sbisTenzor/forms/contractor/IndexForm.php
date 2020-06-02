<?php

namespace app\modules\sbisTenzor\forms\contractor;

use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use yii\data\ActiveDataProvider;

class IndexForm extends \app\classes\Form
{
    /** @var ClientAccount */
    protected $client;

    /**
     * Index form constructor
     * @param ClientAccount|null $client
     */
    public function __construct(ClientAccount $client = null)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Получить запрос на выборку информации по контрагентам
     *
     * @param int $state
     * @return ActiveDataProvider
     */
    public function getDataProvider($state)
    {
        $query = ClientAccount::find();

        $query->where(['IS NOT', 'exchange_group_id', null]);

        if ($this->client) {
            $query->andWhere(['id' => $this->client->id]);
        }

        switch ($state) {
            case SBISExchangeStatus::APPROVED:
                $query->andWhere(['exchange_status' => SBISExchangeStatus::$verified]);
                break;

            case SBISExchangeStatus::DECLINED:
                $query->andWhere(['exchange_status' => SBISExchangeStatus::$notApproved]);
                break;

            case SBISExchangeStatus::UNKNOWN:
                $query->andWhere(['=', 'exchange_status', $state]);
                break;
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Информация по интеграции клиентов';
    }
}