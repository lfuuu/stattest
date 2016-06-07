<?php

namespace app\classes;

use Yii;
use Exception;

/**
 * Генерация документа в формате MHTML (Word2003)
 *
 * Class Html2Mhtml
 * @package app\classes
 */
class Html2Mhtml
{

    private
        $headers = [],
        $headers_exists = [],
        $files = [],
        $boundary;

    /**
     * @param string $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->headers[] = $header;
        $key = mb_strtolower(mb_substr($header, 0, mb_strpos($header, ':', 'UTF-8'), 'UTF-8'), 'UTF-8');
        $this->headers_exists[$key] = true;
        return $this;
    }

    /**
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->setHeader('From: ' . $from);
        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->setHeader('Subject: ' . $subject);
        return $this;
    }

    /**
     * @param string|null $boundary
     * @return $this
     */
    public function setBoundary($boundary = null)
    {
        if (is_null($boundary)) {
            $this->boundary = '--' . strtoupper(md5(mt_rand())) . '_MULTIPART_MIXED';
        } else {
            $this->boundary = $boundary;
        }
        return $this;
    }

    /**
     * @param string $fileName
     * @param string|null $filePath
     * @return $this
     */
    public function addFile($fileName, $filePath = null)
    {
        if (is_null($filePath)) {
            $filePath = $fileName;
        }
        $this->addContents($fileName, file_get_contents($filePath));

        return $this;
    }

    /**
     * @param \Closure|false $callback
     * @return $this
     */
    public function addImages($callback = false)
    {
        foreach ($this->files as &$file) {
            if (preg_match_all('#<img[^>]*src=[\'"]*([^\'"]+)[\'"]*#ui', $file['content'], $matches)) {
                foreach ($matches[1] as $imageSrc) {
                    $fileName = basename($imageSrc);
                    $filePath = $imageSrc;

                    if (is_callable($callback)) {
                        list($fileName, $filePath) = $callback($imageSrc);
                    }

                    $file['content'] = str_replace($imageSrc, $fileName, $file['content']);
                    $this->addFile($fileName, $filePath);
                }
            }
        }

        return $this;
    }

    /**
     * @param \Closure|false $callback
     * @return $this
     */
    public function addMediaFiles($callback = false)
    {
        foreach ($this->files as &$file) {
            if (preg_match_all('#<link[^>]*href=[\'"]*([^\'"]+)[\'"]*#ui', $file['content'], $matches)) {
                foreach ($matches[1] as $src) {
                    $fileName = basename($src);
                    $filePath = $src;

                    if (is_callable($callback)) {
                        list($fileName, $filePath) = $callback($src);
                    }

                    $file['content'] = str_replace($src, $fileName, $file['content']);
                    $this->addFile($fileName, $filePath);

                }
            }
        }

        return $this;
    }

    /**
     * @param string $file_path
     * @param string $content
     * @param \Closure|false $callback
     * @return $this
     */
    public function addContents($filePath, $content, $callback = false)
    {
        if (is_callable($callback)) {
            $content = $callback($content);
        }

        $this->files[] = [
            'file_path' => $filePath,
            'content' => $content,
        ];

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFile()
    {
        $this->checkHeaders();
        if (!$this->checkFiles()) {
            throw new Exception('No file was added');
        }

        $contents = implode(PHP_EOL, $this->headers);
        $contents .= PHP_EOL;
        $contents .= 'MIME-Version: 1.0' . PHP_EOL;
        $contents .= 'Content-Type: multipart/related;' . PHP_EOL;
        $contents .= "\tboundary=\"" . $this->boundary . "\";" . PHP_EOL;
        $contents .= "\ttype=\"text/html\"" . PHP_EOL;
        $contents .= PHP_EOL;
        $contents .= 'This is a multi-part message in MIME format.' . PHP_EOL;
        $contents .= PHP_EOL;

        foreach ($this->files as $file) {
            $contents .= '--' . $this->boundary . PHP_EOL;
            $contents .= 'Content-Type: ' . (mime_content_type($file['file_path']) ?: 'text/html') . PHP_EOL;
            $contents .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
            $contents .= 'Content-Location: ' . $file['file_path'] . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= chunk_split(base64_encode($file['content']), 76);
            $contents .= PHP_EOL;
        }

        $contents .= '--' . $this->boundary . '--' . PHP_EOL;

        return $contents;
    }

    private function checkHeaders()
    {
        if (!array_key_exists('date', $this->headers_exists)) {
            $this->setHeader('Date: ' . date('D, d M Y H:i:s O' . time()));
        }
        if (is_null($this->boundary)) {
            $this->setBoundary();
        }
    }

    /**
     * @return bool
     */
    private function checkFiles()
    {
        return !(count($this->files) == 0);
    }

}