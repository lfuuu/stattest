<?php

namespace app\classes\api;

use app\classes\HttpClient;
use yii\base\InvalidConfigException;

class ApiMatrixElementChat
{
    private ?string $token = null;
    private string $host = 'https://matrix.mcn.hu';

    public static function isAvailable(): bool
    {
        return (bool)\Yii::$app->params['matrix_notifier_token'] ?? false;
    }

    public function __construct()
    {
        $this->token = \Yii::$app->params['matrix_notifier_token'] ?? false;

        if (!$this->token) {
            throw new InvalidConfigException('token not set');
        }
    }

    private function _exec($path, $isPost = false, $data = [])
    {
        $url = $this->host . $path;

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isPost ? 'post' : 'get')
            ->setData($data)
            ->setUrl($url)
            ->auth([
                'method' => 'bearer',
                'token' => $this->token,
            ])
            ->getResponseDataWithCheck();
    }

    public function searchUsers($pattern, $limit = 10)
    {
        return $this->_exec('/_matrix/client/v3/user_directory/search', true, ["limit" => $limit, "search_term" => $pattern]);
    }

    public function getPrivateRooms($userId)
    {
        $result = $this->_exec('/_matrix/client/v3/joined_rooms');

        return $result['joined_rooms'];
    }

    public function getRoomMembers($roomId)
    {
        $result = $this->_exec('/_matrix/client/v3/rooms/' . $roomId . '/members');

        return $result['chunk'];
    }

    public function sendMessageInRoom($roomId, $msg)
    {
        return $this->_exec('/_matrix/client/r0/rooms/' . $roomId . '/send/m.room.message', true, ['msgtype' => 'm.text', 'body' => $msg]);
    }

    public function createPrivateRoom($inviteUserIds)
    {
        if (!is_array($inviteUserIds)) {
            $inviteUserIds = [$inviteUserIds];
        }

        $result = $this->_exec('/_matrix/client/v3/createRoom', true, [
                "preset" => "trusted_private_chat",
                "visibility" => "private",
                "invite" => $inviteUserIds,
                "is_direct" => true,
                "initial_state" => [
                    [
                        "type" => "m.room.guest_access",
                        "state_key" => "",
                        "content" => [
                            "guest_access" => "can_join"
                        ]
                    ]
                ]
            ]
        );

        return $result['room_id'];
    }

    public function getUserPrivateRoomId($userId)
    {
        $rooms = $this->getPrivateRooms($userId);

        foreach ($rooms as $roomId) {
            $members = $this->getRoomMembers($roomId);
            foreach ($members as $member) {
                if ($member['user_id'] == $userId || $member['state_key'] == $userId) {
                    return $roomId;
                }
            }
        }
    }

}