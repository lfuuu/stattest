<?php

namespace app\modules\nnp\classes;

class RouteMncDownloader
{

    const URL_FILE_LIST = 'https://www.niir.ru/bdpn/tablica-marshrutnyh-nomerov/';
    const URL_SITE = 'https://www.niir.ru';

    const downloadDir = './runtime';

    private $page = null;
    private $fileList = [];
    private $file = null;

    public function loadPage()
    {
        $this->page = file_get_contents(self::URL_FILE_LIST);

        if (!$this->page) {
            throw new \ErrorException('Page not load');
        }

        return $this;
    }

    public function parseFiles()
    {
        $m = [];

        preg_match_all("/href='(?'url'\/wp-content\/uploads\/bdpnfiles\/number_range\/number_range_auto-(?'day'\d{1,2})_(?'month'\d{1,2})_(?'year'20\d{2}).xls)'/u", $this->page, $m, PREG_SET_ORDER);

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

        $handleSrc = fopen(self::URL_SITE . $this->file, 'r');

        if (!$handleSrc) {
            throw new \Exception('handleSrc is null');
        }

        $handleDst = fopen($localFile, 'w+');
        if (!$handleDst) {
            throw new \Exception('handleDst is null');
        }

        while ($c = stream_get_contents($handleSrc, 1024)) {
            fwrite($handleDst, $c);
        }

        fclose($handleSrc);
        fclose($handleDst);

        return $fileOnly;
    }
}