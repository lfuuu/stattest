<?php

namespace app\classes\adapters;

use app\classes\dictionary\SwaggerPathMethodMap;
use app\classes\Singleton;
use app\classes\Utils;
use app\models\EventQueue;
use RdKafka\Message;

class EventBus extends Singleton
{
    const TOPIC = 'event-bus-cmd';

    public function listen()
    {
        EbcKafka::me()->getMessage(self::TOPIC, function (Message $message) {
            if (!($message->headers['version'] ?? false)) {
                echo PHP_EOL . ' -- no message version';
                return false;
            }

            echo PHP_EOL . ($message->headers['type'] ?? '') . ' ' . ($message->headers['src'] ?? '') . '/' . ($message->headers['dst'] ?? '') . ' cmd: ' . ($message->headers['cmd'] ?? '');
            if (
                !(
                    isset($message->headers['dst']) && $message->headers['dst'] == 'stat'
                    && isset($message->headers['type']) && $message->headers['type'] == 'cmd'
                )
            ) {
                echo ' -- skip message';
                return true;
            }

            $arrayMessage = (array)$message;
            $arrayMessage['payload'] = Utils::fromJson($message->payload);

            return EventQueue::go(EventQueue::EVENT_BUS_CMD, $arrayMessage);
        });
    }

    public function applyCmd($params)
    {
        if (is_string($params)) {
            $params = Utils::fromJson($params);
        }

        $msg = $params['payload'];

        $result = $this->execCurlCmd($msg);

        $headers = [
                'version' => 1,
                'type' => 'cmd_result',
            ]
            + ($msg['dst'] ? ['src' => $msg['dst']] : [])
            + ($msg['src'] ? ['dst' => $msg['src']] : []);

        EventQueue::go(EventQueue::EVENT_BUS_CMD_RESULT, [
            'payload' => [
                    'id' => $msg['id'],
                    'result' => $result,
                ] + $headers,
            'id' => $msg['id'],
            'headers' => $headers,
        ]);
    }

    public function sendCmdResult($params)
    {
        if (is_string($params)) {
            $params = Utils::fromJson($params);
        }

        if (!isset($params['payload'])) {
            return false;
        }

        return EbcKafka::me()->sendMessage(self::TOPIC,
            $params['payload'],
            $params['id'] ?? Utils::genUUID(),
            $params['headers'] ?? []
        );
    }

    public function testCmd()
    {
        EbcKafka::me()->sendMessage(self::TOPIC, [
            'id' => $id = Utils::genUUID(),
            'version' => 1,
            'src' => 'tester',
            'dst' => 'stat',
            'type' => 'cmd',
            'cmd' => '/api/internal/client/get-business-list',
//            'cmd' => '/api/internal/uu/add-account-tariff',
//            'argv' => [
//                'client_account_id' => 4,
//                'service_type_id' => 2,
//                'tariff_period_id' => 15,
//                'voip_number' => '74992130008',
//                'is_async' => 0,
//                'is_create_user' => 1,
//            ]
        ],
            $id,
            [
                'version' => 1,
                'type' => 'cmd',
                'dst' => 'stat',
                'src' => 'tester',
            ]);
    }

    private function execCurlCmd($msg)
    {
        $apiKey = \Yii::$app->params['API_SECURE_KEY'];
        $siteUrl = isset($_SERVER['IS_TEST']) && $_SERVER['IS_TEST'] == 1 ? 'http://127.0.0.1/' : \Yii::$app->params['SITE_URL'];
        $queryString = isset($msg['argv']) ? '-d \'' . http_build_query($msg['argv']) . '\'' : '';
        $method = SwaggerPathMethodMap::me()->getMethod($msg['cmd']);
        $command = "curl --show-error -s -X {$method} --header 'Content-Type: application/x-www-form-urlencoded' --header 'Accept: application/json' --header 'Authorization: Bearer {$apiKey}' {$queryString} '{$siteUrl}{$msg['cmd']}' 2>&1";

        @ob_start();
        system($command);
        $result = ob_get_clean();

        if (preg_match('/curl: \((\d+)\) (.*)/', $result, $m)) {
            $result = [
                "status" => "ERROR",
                "result" => $m[2],
                "code" => ($m[1] + 10000),
            ];
        }

        if (preg_match("/<title>(.*)<\/title>/", $result, $m)) {
            if (preg_match("/(.*)\s*\(#?(.*)\)/", $m[1], $mm)) {
                $result = [
                    "status" => "ERROR",
                    "result" => $mm[1],
                    "code" => ($mm[2] + 10000),
                ];
            } else {
                $result = [
                    "status" => "ERROR",
                    "result" => $m[1],
                    "code" => 10001,
                ];
            }
        }

        return json_decode($result, true) ?? $result;
    }
}