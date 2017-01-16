<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use Yii;
use yii\base\Exception;

/**
 * Class ApiVmCollocation
 * @package app\classes\api
 * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=3508161
 * @link http://doc.ispsystem.ru/index.php/VMmanager_API
 * @link http://doc.ispsystem.ru/index.php/%D0%92%D0%B7%D0%B0%D0%B8%D0%BC%D0%BE%D0%B4%D0%B5%D0%B9%D1%81%D1%82%D0%B2%D0%B8%D0%B5_%D1%87%D0%B5%D1%80%D0%B5%D0%B7_API
 */
class ApiVmCollocation
{
    const FUNC_USER_EDIT = 'user.edit';
    const FUNC_USER_DISABLE = 'user.suspend';
    const FUNC_USER_ENABLE = 'user.resume';

    const FUNC_VM_EDIT = 'vm.edit';
    const FUNC_VM_DELETE = 'vm.extdelete';

    protected static $_instance = null;

    /**
     * singletone
     */
    protected function __construct()
    {
    }

    /**
     * singletone
     */
    protected function __clone()
    {
    }

    /**
     * singletone
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->getUrl() && $this->getAuthinfo();
    }

    /**
     * @return string
     */
    protected function getConfig($param)
    {
        return Yii::$app->params['vmCollocation'][$param];
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return $this->getConfig('url');
    }

    /**
     * @return string
     */
    protected function getAuthinfo()
    {
        return $this->getConfig('authinfo');
    }

    /**
     * Отправить данные
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function exec($data, $out = 'JSONdata')
    {
        if (!$this->isAvailable()) {
            throw new Exception('API VM collocation is not configured');
        }

        $data = array_merge($data, [
            'out' => $out,
            'sok' => 'ok',
            'authinfo' => $this->getAuthinfo(),
        ]);
        $result = JSONQuery::exec($this->getUrl(), $data, false);

        if (isset($result['error']) && $result['error']) { // $result['error'] = [code => 'exists', obj => 'user', msg => 'userid40821user.editThe __value__  already exists The id40821  already exists ']
            $msg = print_r([$data, $result], true);
            throw new Exception($msg);
        }

        return $result;
    }

    /**
     * Создать VM-юзера и вернуть его elid
     *
     * @param $name
     * @param $password
     * @return int|null
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
        return (int)$result['id'];
    }

    /**
     * Включить/выключить VM-юзера
     *
     * @param int $elid
     * @param bool $isEnable
     * @return array
     */
    public function enableOrDisableUser($elid, $isEnable)
    {
        $data = [
            'func' => $isEnable ? self::FUNC_USER_ENABLE : self::FUNC_USER_DISABLE,
            'elid' => $elid,
        ];
        $result = $this->exec($data, $out = 'json'); // какой-то баг в VM manager. Именно для этого метода он не поддерживает JSONdata
        return $result['doc'];
    }

    /**
     * Создать VPS
     *
     * @param $name
     * @param $password
     * @param $domain
     * @param $preset
     * @param $clientId
     * @return int
     */
    public function createVps($name, $password, $domain, $preset, $clientId)
    {
        $data = [
            'func' => self::FUNC_VM_EDIT,
            'name' => $name,
            'user' => $clientId,
            'fstype' => 'simfs',
            'password' => $password,
            'confirm' => $password,
            'domain' => $domain,
            'preset' => $preset, // http://datacenter.mcn.ru/vps-hosting/    Optimum - 4, Premium - 3, Standart - 2
        ];
        $result = $this->exec($data); // $result = [doc, ip, id, elid, hostnode, ok]
        return (int) $result['elid'];
    }

    /**
     * Обновить VPS
     *
     * @param int $vmId
     * @param int $resourceRam
     * @param int $resourceHdd
     * @param int $resourceProcessor
     * @return string
     */
    public function updateVps($vmId, $resourceRam, $resourceHdd, $resourceProcessor)
    {
        $data = [
            'func' => self::FUNC_VM_EDIT,
            'elid' => $vmId,
            'mem' => $resourceRam,
            'hdd' => $resourceHdd,
            'vcpu' => $resourceProcessor,
        ];
        $result = $this->exec($data); // $result = [doc, elid, ok]
        return (int) $result['doc'];
    }

    /**
     * Удалить VPS
     *
     * @param int $vmId
     * @return string
     */
    public function dropVps($vmId)
    {
        $data = [
            'func' => self::FUNC_VM_DELETE,
            'elid' => $vmId,
        ];
        $result = $this->exec($data);
        return $result['doc'];
    }
}
