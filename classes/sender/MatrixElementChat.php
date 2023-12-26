<?php

namespace app\classes\sender;

use app\classes\api\ApiMatrixElementChat;
use app\classes\Singleton;
use app\models\User;

class MatrixElementChat extends Singleton
{
    private ?ApiMatrixElementChat $api = null;

    /**
     * @param array $param
     * @return bool
     */
    public function sendTroubleNotifier($troubleId, $user, $text)
    {
        if (!$troubleId || !$text || !$user) {
            return false;
        }

        if (!$this->api) {
            $this->api = new ApiMatrixElementChat();
        }

        /** @var User $user */
        $user = User::findByUsername($user);

        if (!$user || !$user->phone_work) {
            return false;
        }

        $text .= "\n" . \Yii::$app->params['SITE_URL'] . "?module=tt&action=view&id=" . $troubleId;

        return $this->searchUserAndSend($user, $text);
    }

    private function searchUserAndSend(User $user, $text)
    {
        $roomId = $this->searchUserRoom($user);

        return $this->api->sendMessageInRoom($roomId, $text);
    }

    private function searchUserRoom(User $user)
    {
        $userId = $this->getUserIdByUser($user);

        if (!$userId) {
            throw new \LogicException('user not found: ' . $user->user);
        }

        $roomId = $this->api->getUserPrivateRoomId($userId);

        if (!$roomId) {
            $roomId = $this->api->createPrivateRoom($userId);
        }

        return $roomId;
    }

    private function getUserIdByUser(User $user)
    {
        $users = $this->api->searchUsers($user->phone_work);

        if (!$users || !isset($users['results'])) {
            throw new \LogicException('bad answer');
        }

        if (count($users['results']) == 1) {
            return $users['results'][0]['user_id'];
        }

        return false;
    }
}