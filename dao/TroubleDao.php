<?php

namespace app\dao;

use app\classes\Assert;
use app\classes\enum\DepartmentEnum;
use app\classes\helpers\ArrayHelper;
use app\classes\helpers\DependecyHelper;
use app\classes\helpers\TTCounterHelper;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Param;
use app\models\support\Ticket;
use app\models\Trouble;
use app\models\TroubleFolder;
use app\models\TroubleRoistat;
use app\models\TroubleRoistatStore;
use app\models\TroubleStage;
use app\models\TroubleState;
use app\models\TroubleType;
use app\models\User;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use Exception;
use yii\caching\TagDependency;
use yii\db\Expression;

/**
 * Class TroubleDao
 *
 * @method static TroubleDao me($args = null)
 */
class TroubleDao extends Singleton
{
    const MODE_GET = 1;
    const MODE_QUEUE_FOR_RECOUNT = 2;
    const MODE_RECOUNT = 3;

    /**
     * Получить количество заявок залогиненного пользователя
     *
     * @param string $user
     * @return int
     */
    public function getMyTroublesCount($user = null)
    {
        return
            Trouble::find()
                ->alias('t')
                ->innerJoin(['s' => 'tt_stages'], 's.stage_id = t.cur_stage_id and s.trouble_id = t.id')
                ->where([
                    's.user_main' => $user ?: \Yii::$app->user->getIdentity()->user,
                    't.is_closed' => 0,
                ])
                ->andWhere(['<=', 's.date_start', new Expression('NOW()')])
                ->count();
    }

    /**
     * Получение ID серверных траблов по ЛС
     *
     * @param ClientAccount $client
     * @return array
     */
    public function getServerTroublesIDsForClient(ClientAccount $client)
    {
        return
            Trouble::find()
                ->select('tt.id')
                ->from(['tt' => 'tt_troubles'])
                ->where(
                    [
                        'and',
                        ['>', 'tt.`server_id`', 0],
                        ['=', 'tt.is_closed', 0],
                        ['=', 'tt.`client`', $client->client],
                    ]
                )->createCommand()->queryAll();
    }

    /**
     * Создание траблы для заявки тех.поддержки
     *
     * @param integer $clientAccountId
     * @param string $department
     * @param string $subject
     * @param string $description
     * @param integer $supportTicketId
     * @param string|bool $author
     * @throws \Exception
     */
    public function createTroubleForSupportTicket(
        $clientAccountId,
        $department,
        $subject,
        $description,
        $supportTicketId,
        $author = false
    )
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        Assert::isObject($clientAccount);

        $problem = '';
        if ($department) {
            $problem .= 'Отдел: ' . DepartmentEnum::getName($department) . "\n";
        }

        $problem .= 'Тема: ' . $subject . "\n";
        $problem .= $description;

        $supportUser = $this->_getUserByDepartment($department);

        $transaction = Trouble::getDb()->beginTransaction();
        try {
            $trouble = new Trouble();
            $trouble->trouble_type = Trouble::TYPE_TROUBLE;
            $trouble->trouble_subtype = Trouble::SUBTYPE_TROUBLE;
            $trouble->client = $clientAccount->client;
            $trouble->user_author = $author ?: $supportUser;
            $trouble->date_creation = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $trouble->problem = $problem;
            $trouble->folder = Trouble::DEFAULT_SUPPORT_FOLDER;
            $trouble->support_ticket_id = $supportTicketId;
            $trouble->save();

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = Trouble::DEFAULT_SUPPORT_STATE;
            $stage->user_main = $supportUser;
            $stage->date_start = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $stage->date_finish_desired = $stage->date_start;
            $stage->save();

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * Обновить траблу по заявки тех.поддержке
     *
     * @param Ticket $ticket
     */
    public function updateTroubleBySupportTicket(Ticket $ticket)
    {
        $trouble = Trouble::findOne(['support_ticket_id' => $ticket->id]);
        if (!$trouble) {
            return;
        }

        $supportUser = $this->_getUserByDepartment($ticket->department);

        $oldStage = TroubleStage::findOne($trouble->cur_stage_id);
        Assert::isObject($oldStage);

        $newStateId = $ticket->spawnTroubleStatus();
        if ($newStateId != $oldStage->state_id) {
            $oldStage->user_edit = $supportUser;
            $oldStage->save();

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = $newStateId;
            $stage->user_main = $supportUser;
            $stage->date_start = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $stage->save();

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->save();
        }

    }

    /**
     * Обновить заявку тех.поддержке по ID траблы
     *
     * @param integer $troubleId
     */
    public function updateSupportTicketByTrouble($troubleId)
    {
        $trouble = Trouble::findOne($troubleId);
        Assert::isObject($trouble);

        if (!$trouble->support_ticket_id) {
            return;
        }

        $stage = TroubleStage::findOne($trouble->cur_stage_id);
        Assert::isObject($stage);

        $ticket = Ticket::findOne($trouble->support_ticket_id);
        $ticket->setStatusByTroubleState($stage->state_id);
        $ticket->updated_at = (new \DateTime('now', new \DateTimeZone('UTC')))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $ticket->save();
    }

    /**
     * Получение пользователя по отделу
     *
     * @param string $department
     * @return string
     */
    private function _getUserByDepartment($department)
    {
        if ($department == DepartmentEnum::SALES) {
            return Trouble::DEFAULT_SUPPORT_SALES;
        } elseif ($department == DepartmentEnum::ACCOUNTING) {
            return Trouble::DEFAULT_SUPPORT_ACCOUNTING;
        } elseif ($department == DepartmentEnum::TECHNICAL) {
            return Trouble::DEFAULT_SUPPORT_TECHNICAL;
        } else {
            return Trouble::DEFAULT_SUPPORT_USER;
        }
    }

    /**
     * Добавить этап к заявке
     *
     * @param Trouble $trouble
     * @param integer $newStateId
     * @param string $comment
     * @param integer $newUserMainId
     * @param integer $userEditId
     * @return TroubleStage
     * @throws \Exception
     */
    public function addStage($trouble, $newStateId, $comment, $newUserMainId = null, $userEditId = null)
    {
        if (!$userEditId) {
            $userEdit = \Yii::$app->user->getIdentity();
        } else {
            $userEdit = User::findOne(["id" => $userEditId]);
        }

        $userMain = null;
        if ($newUserMainId) {
            $userMain = User::findOne(["id" => $newUserMainId]);
        }

        Assert::isObject($userEdit);
        Assert::isObject($trouble);

        $curStage = $trouble->currentStage;
        Assert::isObject($curStage);

        $newState = TroubleState::findOne(['id' => $newStateId]);

        Assert::isObject($newState);

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $curStage->user_edit = $userEdit->user;

            if (trim($comment)) {
                $curStage->comment = $comment;
            }

            if (!$curStage->save()) {
                throw new ModelValidationException($curStage);
            }

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = $newStateId;
            $stage->user_main = $userMain ? $userMain->user : $curStage->user_main;
            $stage->date_start = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            if (!$stage->save()) {
                throw new ModelValidationException($stage);
            }

            $trouble->cur_stage_id = $stage->stage_id;
            $trouble->folder = $newState->folder;
            $trouble->is_closed = $newState->is_final;

            if (!$trouble->save()) {
                throw new ModelValidationException($trouble);
            }
            $this->setChanged($trouble);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $stage;
    }

    /**
     * Создание заявки
     *
     * @param integer $clientAccountId
     * @param string $type
     * @param string $subtype
     * @param string $troubleText
     * @param string $author
     * @param string $user
     * @param array $options
     * @return Trouble
     * @throws \Exception
     * @internal param string $department
     * @internal param string $subject
     * @internal param string $description
     * @internal param int $supportTicketId
     * @internal param bool|string $author
     */
    public function createTrouble(
        $clientAccountId,
        $type,
        $subtype,
        $troubleText,
        $author = null,
        $user = null,
        $options = []
    )
    {
        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        Assert::isObject($clientAccount);

        $transaction = Trouble::getDb()->beginTransaction();
        try {
            $trouble = new Trouble();
            $trouble->trouble_type = $type;
            $trouble->trouble_subtype = $subtype;
            $trouble->client = $clientAccount->client;
            $trouble->user_author = $author ?: Trouble::DEFAULT_API_AUTHOR;
            $trouble->date_creation = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $trouble->problem = $troubleText;
            $trouble->folder = Trouble::DEFAULT_CONNECT_FOLDER;
            if (!$trouble->save()) {
                throw new ModelValidationException($trouble);
            }

            $stage = new TroubleStage();
            $stage->trouble_id = $trouble->id;
            $stage->state_id = Trouble::DEFAULT_CONNECT_STATE;
            $stage->user_main = $user ?: $trouble->user_author;
            $stage->date_start = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
            $stage->date_finish_desired = $stage->date_start;
            if (!$stage->save()) {
                throw new ModelValidationException($stage);
            }

            $trouble->cur_stage_id = $stage->stage_id;
            if (!$trouble->save()) {
                throw new ModelValidationException($trouble);
            }

//            if (isset($options[Trouble::OPTION_IS_CHECK_SAVED_ROISTAT_VISIT])) {
//                if ($roistatVisit = TroubleRoistatStore::getRoistatIdByAccountId($clientAccount->id)) {
//                    $options['roistat_visit'] = $roistatVisit;
//                }
//            }

            if (isset($options['roistat_visit'])) {
                $troubleRoistat = new TroubleRoistat();
                $troubleRoistat->trouble_id = $trouble->id;
                $troubleRoistat->roistat_visit = $options['roistat_visit'];

                if (!$troubleRoistat->save()) {
                    throw new ModelValidationException($troubleRoistat);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $trouble;
    }

    /**
     * Оповещение о создании услуги
     *
     * @param AccountTariff $accountTariff
     * @param AccountTariffLog $accountTariffLog
     * @param array $post
     * @throws \Exception
     */
    public function notificateCreateAccountTariff(AccountTariff $accountTariff, AccountTariffLog $accountTariffLog, $post = [], $options = [])
    {
        $userUser = Trouble::DEFAULT_ADD_ACCOUNT_TARIFF_USER;

        if (
            $accountTariff->client_account_id
            && $accountTariff->clientAccount->contract
            && $accountTariff->clientAccount->contract->account_manager
        ) {
            $userUser = $accountTariff->clientAccount->contract->account_manager;
        }

        $user = User::findOne(['user' => $userUser]);

        $troubleTexts = [];

        if ($accountTariffLog->tariff_period_id) {
            if (count($accountTariff->accountTariffLogs) >= 2) {
                $text = 'УУ смена тарифа';
            } else {
                $text = 'УУ создана';
            }
        } else {
            $text = 'УУ закрыта';
        }

        $troubleTexts[] = $post ? 'Ошибка содания УУ' : $text;
        $troubleTexts[] = 'ЛС ' . $accountTariff->client_account_id;
        $troubleTexts[] = 'Тип ' . $accountTariff->serviceType->name;
        $troubleTexts[] = 'Тариф ' . $accountTariffLog->getName();

        if ($accountTariff->service_type_id == ServiceType::ID_VOIP) {
            $troubleTexts[] = 'Номер ' . $accountTariff->voip_number;
        }

        if ($post) {
            $troubleTexts[] = nl2br(print_r($post, true));
        }

        $troubleText = implode('.<br/>', $troubleTexts);

        $this->createTrouble($accountTariff->client_account_id, Trouble::TYPE_CONNECT, Trouble::SUBTYPE_CONNECT, $troubleText, null, ($user ? $user->user : null), $options);

        if ($user && $user->email && $accountTariffLog->tariff_period_id) {
            // отправить письмо только при создании, но не при закрытии
            \Yii::$app->mailer
                ->compose()
                ->setHtmlBody($troubleText)
                ->setFrom(\Yii::$app->params['adminEmail'])
                ->setTo($user->email)
                ->setSubject($post ? '[UU] Ошибка заказа услуги' : '[UU] Заказ услуги')
                ->send();
        }
    }

    /**
     * Список ID закрывающих статусов этапа
     *
     * @return array
     */
    public function getClosedStatesId()
    {
        static $cache = null;

        if (!$cache) {
            $cache = TroubleState::find()
                ->select('id')
                ->where(['is_final' => true])
                ->column();
        }

        return $cache;
    }

    /**
     * Названия отказных и мусорных статусов
     *
     * @return string[]
     */
    public function getRejectStatusesName()
    {
        return ['Отказ', 'Мусор'];
    }

    /**
     * Список ID отказных и мусорных статусов этапа
     *
     * @return array
     */
    public function getRejectStatesId()
    {
        static $cache = null;

        if (!$cache) {
            $cache = TroubleState::find()
                ->select('id')
                ->where(['name' => $this->getRejectStatusesName()])
                ->column();
        }

        return $cache;
    }

    /**
     * Установить все закрыты траблы явно
     *
     * @return int
     */
    public function setTroublesClosed()
    {
        $sql = <<<SQL
UPDATE tt_troubles t
  INNER JOIN `tt_stages` `s` ON s.stage_id = t.cur_stage_id AND s.trouble_id = t.id
  INNER JOIN `tt_states` `st` ON `s`.`state_id` = `st`.id
SET is_closed = 1
WHERE
  st.is_final = 1
SQL;

        return Trouble::getDb()->createCommand($sql)->execute();
    }

    /**
     * Получить счететчики траблов
     *
     * @param integer $mode 1 - получить из кеша, 2 - поставить в очередь на пересчет, 3 - пересчитать кеш
     * @param string $trouble_type
     * @return array|mixed
     */
    public function getTaskFoldersCount($mode = self::MODE_GET, $trouble_type = Trouble::TYPE_TASK)
    {
        $key = 'tt-folder-' . $trouble_type . '-count';
        if ($mode === self::MODE_GET && \Yii::$app->cache->exists($key)) {
            return \Yii::$app->cache->get($key);
        }
        $ttd = TTCounterHelper::getTroubleTypeData(['pk', 'folders'], $trouble_type);
        $arr = reset($ttd);
        $pk = isset($arr['pk']) ? $arr['pk'] : null;
        $folder = isset($arr['folders']) ? $arr['folders'] : null;
        if (!$pk || !$folder) {
            throw new Exception('Отсутствует folder или pk');
        }
        if ($mode === self::MODE_QUEUE_FOR_RECOUNT) {
            TTCounterHelper::addPkTroubleTypeToRecalc($pk);
            return;
        }

        $sql = <<<SQL
SELECT
  tf.pk,
  tf.name,
  COUNT(DISTINCT (T.id)) AS cnt
FROM
  tt_troubles AS T
  LEFT JOIN
  tt_folders AS tf ON T.folder & tf.pk
WHERE
  ((T.server_id = :server_id) AND (T.trouble_type = :trouble_type) AND (tf.pk & :folder))
GROUP BY tf.pk
ORDER BY `tf`.`order`

SQL;
        $result = ArrayHelper::index(Trouble::getDb()->createCommand($sql, [':server_id' => 0, ':trouble_type' => $trouble_type, ':folder' => $folder])->queryAll(), 'pk');
        \Yii::$app->cache->set($key, $result, DependecyHelper::TIMELIFE_HOUR, (new TagDependency(['tags' => DependecyHelper::TAG_TROUBLE_COUNT])));

        return $result;
    }

    /**
     * Утсанавливаем, что трабла изменилась
     *
     * @param Trouble $trouble
     */
    public function setChanged(Trouble $trouble)
    {
        $trouble_type = $trouble->trouble_type;
        if (!$trouble_type) {
            $trouble_type = Trouble::TYPE_TASK;
        }
        $this->updateSupportTicketByTrouble($trouble->id);
        $this->getTaskFoldersCount(self::MODE_QUEUE_FOR_RECOUNT, $trouble_type);
    }

}
