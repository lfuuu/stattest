<?php

namespace app\modules\sbisTenzor\forms\contractor;

use app\models\ClientAccount;
use app\modules\sbisTenzor\models\SBISContractor;
use yii\data\ActiveDataProvider;

class RoamingForm extends \app\classes\Form
{
    /** @var ClientAccount */
    protected $client;

    /**
     * RoamingForm constructor
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
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = SBISContractor::find();

        $query
            ->with(['clientAccount'])
            ->where(['is_roaming' => true])
            ->andWhere(['IS NOT', 'account_id', null])
            ->orderBy([
                'full_name' => SORT_ASC,
                'account_id' => SORT_ASC,
                'branch_code' => SORT_ASC,
                'exchange_id' => SORT_ASC,
            ])
        ;

        if ($this->client) {
            $query->andWhere(['account_id' => $this->client->id]);
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
        return 'Информация по контрагентам с роумингом';
    }
}