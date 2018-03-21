<?php

namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiVmCollocation
 *
 * @method static ApiVmCollocation me($args = null)
 *
 * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=3508161
 * @link http://doc.ispsystem.ru/index.php/VMmanager_API
 * @link http://doc.ispsystem.ru/index.php/%D0%92%D0%B7%D0%B0%D0%B8%D0%BC%D0%BE%D0%B4%D0%B5%D0%B9%D1%81%D1%82%D0%B2%D0%B8%D0%B5_%D1%87%D0%B5%D1%80%D0%B5%D0%B7_API
 */
class ApiVmCollocation extends Singleton
{
    const FUNC_USER_EDIT = 'user.edit';
    const FUNC_USER_DISABLE = 'user.suspend';
    const FUNC_USER_ENABLE = 'user.resume';

    const FUNC_VM_EDIT = 'vm.edit';
    const FUNC_VM_DELETE = 'vm.extdelete';

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->_getUrl() && $this->_getAuthinfo();
    }

    /**
     * @param string $param
     * @return string|array
     */
    private function _getConfig($param)
    {
        return Yii::$app->params['vmCollocation'][$param];
    }

    /**
     * @return string
     */
    private function _getUrl()
    {
        return $this->_getConfig('url');
    }

    /**
     * @return string
     */
    private function _getAuthinfo()
    {
        return $this->_getConfig('authinfo');
    }

    /**
     * Отправить данные
     *
     * @param array $data
     * @param string $out
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function exec($data, $out = 'JSONdata')
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API VM collocation is not configured');
        }

        $data = array_merge($data, [
            'out' => $out,
            'sok' => 'ok',
            'authinfo' => $this->_getAuthinfo(),
        ]);

        return (new HttpClient)
            ->createRequest()
            ->setMethod('get')
            ->setData($data)
            ->setUrl($this->_getUrl())
            ->getResponseDataWithCheck();
    }

    /**
     * Создать VM-юзера и вернуть его elid
     *
     * @param string $name
     * @param string $password
     * @return int
     * @throws InvalidConfigException
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function createUser($name, $password)
    {
        $data = [
            'func' => self::FUNC_USER_EDIT,
            'name' => $name,
            'passwd' => $password,
            'confirm' => $password,
        ];
        $result = $this->exec($data); // $result = [doc => 'UserAdministrator 16id40821develuser.edit', id => 33, ok => '']

        if ($result['id']) {
            $data['result'] = (int)$result['id'];
            // создать важное событие
            ImportantEvents::create(
                ImportantEventsNames::VM_CREATE_USER,
                ImportantEventsSources::SOURCE_STAT,
                $data);
        }

        return (int)$result['id'];
    }

    /**
     * Включить/выключить VM-юзера
     *
     * @param int $elid
     * @param bool $isEnable
     * @return array
     * @throws InvalidConfigException
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function enableOrDisableUser($elid, $isEnable)
    {
        $data = [
            'func' => $isEnable ? self::FUNC_USER_ENABLE : self::FUNC_USER_DISABLE,
            'elid' => $elid,
        ];
        $result = $this->exec($data, $out = 'json'); // какой-то баг в VM manager. Именно для этого метода он не поддерживает JSONdata

        if ($result['doc']) {
            $data['result'] = $result['doc'];
            // создать важное событие
            ImportantEvents::create(
                $isEnable ? ImportantEventsNames::VM_ENABLE_USER : ImportantEventsNames::VM_DISABLE_USER,
                ImportantEventsSources::SOURCE_STAT,
                $data);
        }

        return $result['doc'];
    }

    /**
     * Создать VPS
     *
     * @param string $name
     * @param string $password
     * @param string $domain
     * @param string $preset
     * @param int $clientId
     * @return int
     * @throws InvalidConfigException
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function createVps($name, $password, $domain, $preset, $clientId)
    {
        $data = [
            'func' => self::FUNC_VM_EDIT,
            'name' => $name,
            'user' => $clientId,
            'password' => $password,
            'confirm' => $password,
            'domain' => $domain,
            'preset' => $preset, // http://datacenter.mcn.ru/vps-hosting/    Optimum - 4, Premium - 3, Standart - 2
        ];
        $data = array_merge($this->_getConfig('createVpsParams'), $data);
        $result = $this->exec($data); // $result = [doc, ip, id, elid, hostnode, ok]

        if ($result['elid']) {
            $data['result'] = (int)$result['elid'];
            // создать важное событие
            ImportantEvents::create(
                ImportantEventsNames::VM_CREATE_VPS,
                ImportantEventsSources::SOURCE_STAT,
                $data);
        }

        return (int)$result['elid'];
    }

    /**
     * Обновить VPS
     *
     * @param int $vmId
     * @param int $resourceRam
     * @param int $resourceProcessor
     * @return string
     * @throws InvalidConfigException
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function updateVps($vmId, $resourceRam, $resourceProcessor)
    {
        $data = [
            'func' => self::FUNC_VM_EDIT,
            'elid' => $vmId,
            'mem' => $resourceRam,
            'vcpu' => $resourceProcessor,
        ];
        $result = $this->exec($data); // $result = [doc, elid, ok]

        if ($result['doc']) {
            $data['result'] = $result['doc'];
            // создать важное событие
            ImportantEvents::create(
                ImportantEventsNames::VM_UPDATE_VPS,
                ImportantEventsSources::SOURCE_STAT,
                $data);
        }

        return $result['doc'];
    }

    /**
     * Удалить VPS
     *
     * @param int $vmId
     * @return string
     * @throws InvalidConfigException
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function dropVps($vmId)
    {
        $data = [
            'func' => self::FUNC_VM_DELETE,
            'elid' => $vmId,
        ];
        $result = $this->exec($data);

        if ($result['doc']) {
            $data['result'] = $result['doc'];
            // создать важное событие
            ImportantEvents::create(
                ImportantEventsNames::VM_DROP_VPS,
                ImportantEventsSources::SOURCE_STAT,
                $data);
        }

        return $result['doc'];
    }
}
