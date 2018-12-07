<?php
namespace app\modules\nnp\classes;


use kartik\base\Config;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class FtpSsh2Downloader
 */
class FtpSsh2Downloader extends Component
{
    private $_user = null;
    private $_pass = null;
    private $_srcDir = './runtime';

    const HOST = 'prod-sftp.numlex.ru';
    const PORT = 3232;
    const PATH = '/numlex/Port_All';

    private $_connection = null;

    /**
     * Инициализация
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        $module = Config::getModule('nnp');
        $params = $module->params;

        if (!isset($params['numlex_user']) || !$params['numlex_user'] || !isset($params['numlex_pass']) || !$params['numlex_pass']) {
            throw new InvalidConfigException('На заданы login/password для доступа по sftp на prod-sftp.numlex.ru');
        }

        $this->_user = $params['numlex_user'];
        $this->_pass = $params['numlex_pass'];
    }

    /**
     * Подключение
     *
     * @return bool
     */
    public function connect()
    {
        $this->_connection = ssh2_connect(self::HOST, self::PORT);

        return ssh2_auth_password($this->_connection, $this->_user, $this->_pass);
    }

    /**
     * Получаем список файлов
     *
     * @return array
     */
    public function getFiles()
    {
        $sftp = ssh2_sftp($this->_connection);

        $dir = "ssh2.sftp://" . $sftp . self::PATH;

        $handle = opendir($dir);

        $files = [];
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $files[] = $file;
            }
        }
        closedir($handle);

        return $files;
    }

    /**
     * Скачиваем файлы
     *
     * @param string $file
     * @return bool
     * @throws \Exception
     */
    public function downloadFile($file)
    {
        if (is_file($this->_srcDir . '/' . $file)) {
            throw new \LogicException('Файл ' . $file . ' уже скачен');
        }

        $sftp = ssh2_sftp($this->_connection);

        $dir = "ssh2.sftp://" . $sftp . self::PATH;

        $handleSrc = fopen($dir . '/' . $file, 'r');

        if (!$handleSrc) {
            throw new \Exception('handleSrc is null');
        }

        $handleDst = fopen($this->_srcDir . '/' . $file, 'w+');
        if (!$handleDst) {
            throw new \Exception('handleDst is null');
        }

        while ($c = stream_get_contents($handleSrc, 4048)) {
            fwrite($handleDst, $c);
        }

        fclose($handleSrc);
        fclose($handleDst);

        return true;
    }
}