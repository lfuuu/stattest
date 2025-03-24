<?php

namespace app\modules\nnp\classes;

class RouteMncDownloader
{

    const URL_FILE_LIST = 'https://www.nic-t.ru/bdpn/tablica-marshrutnyh-nomerov/';
    const URL_SITE = 'https://www.nic-t.ru';

    const downloadDir = './runtime';

    private $page = null;
    private $fileList = [];
    private $file = null;

    public function loadPage()
    {
//        $this->page = file_get_contents(self::URL_FILE_LIST);
        $this->page = shell_exec("curl --tlsv1.3 --tls-max 1.3 -L '" . self::URL_FILE_LIST . "'");

        if (!$this->page) {
            throw new \ErrorException('Page not load');
        }

        return $this;
    }

    public function parseFiles()
    {
        $m = [];

        preg_match_all("/href=\"(?'url'\/wp-content\/uploads\/bdpnfiles\/number_range\/number_range_auto-(?'day'\d{1,2})_(?'month'\d{1,2})_(?'year'20\d{2}).xls)\"/u", $this->page, $m, PREG_SET_ORDER);

        if (!$m) {
            throw new \LogicException('File list empty');
        }

        $this->fileList = [];

        array_walk($m, function ($v) {
            $this->fileList[$v['year'] . '-' . $v['month'] . '-' . $v['day']] = $v['url'];
        });

        return $this;
    }

    public function findLast()
    {
        if (!$this->fileList) {
            throw new \LogicException('File list empty');
        }

        $keys = array_keys($this->fileList);
        sort($keys);
        $lastDate = end($keys);

        $this->file = $this->fileList[$lastDate];

        return $this;
    }

    private function _getFileNameFormUrl($url)
    {
        $parts = explode('/', $url);
        return $parts[count($parts) - 1];
    }

    public function download()
    {
        $fileOnly = $this->_getFileNameFormUrl($this->file);

        $localFile = self::downloadDir . '/' . $fileOnly;

        // already?
        if (is_file($localFile)) {
            if (filesize($localFile) == 0) {
                unlink($localFile);
            } else {
                return $fileOnly;
            }
        }

        shell_exec("curl --tlsv1.3 --tls-max 1.3 -L -o '" . $localFile . "' '" . self::URL_SITE . $this->file . "'");

        if (!is_file($localFile) || !filesize($localFile)) {
            throw new \Exception('file not received');
        }

//        $handleSrc = fopen(self::URL_SITE . $this->file, 'r');
//
//        if (!$handleSrc) {
//            throw new \Exception('handleSrc is null');
//        }
//
//        $handleDst = fopen($localFile, 'w+');
//        if (!$handleDst) {
//            throw new \Exception('handleDst is null');
//        }
//
//        while ($c = stream_get_contents($handleSrc, 1024)) {
//            fwrite($handleDst, $c);
//        }
//
//        fclose($handleSrc);
//        fclose($handleDst);

        return $fileOnly;
    }
}