<?php

namespace app\classes;

use Yii;
use Exception;

class Html2Mhtml
{

    private
        $headers = [],
        $headers_exists = [],
        $files = [],
        $boundary;

    public function setHeader($header)
    {
        $this->headers[] = $header;
        $key = mb_strtolower(mb_substr($header, 0, mb_strpos($header, ':', 'UTF-8'), 'UTF-8'), 'UTF-8');
        $this->headers_exists[$key] = true;
        return $this;
    }

    public function setFrom($from)
    {
        $this->setHeader('From: ' . $from);
        return $this;
    }

    public function setSubject($subject)
    {
        $this->setHeader('Subject: ' . $subject);
        return $this;
    }

    public function setBoundary($boundary = null)
    {
        if (is_null($boundary)) {
            $this->boundary = '--' . strtoupper( md5( mt_rand() ) ) . '_MULTIPART_MIXED';
        }
        else {
            $this->boundary = $boundary;
        }
        return $this;
    }

    public function addFile($file_name, $file_path = null)
    {
        if (is_null($file_path)) {
            $file_path = $file_name;
        }
        $this->addContents($file_name, file_get_contents($file_path));

        return $this;
    }

    public function addImages($callback = false)
    {
        foreach ($this->files as &$file) {
            if (preg_match_all('#<img[^>]*src=[\'"]*([^\'"]+)[\'"]*#ui', $file['content'], $matches)) {
                foreach ($matches[1] as $image_src) {
                    $file_name = basename($image_src);
                    $file_path = $image_src;

                    if (is_callable($callback)) {
                        list($file_name, $file_path) = $callback($image_src);
                    }

                    $file['content'] = str_replace($image_src, $file_name, $file['content']);
                    $this->addFile($file_name, $file_path);
                }
            }
        }

        return $this;
    }

    public function addMediaFiles($callback = false)
    {
        foreach ($this->files as &$file) {
            if (preg_match_all('#<link[^>]*href=[\'"]*([^\'"]+)[\'"]*#ui', $file['content'], $matches)) {
                foreach ($matches[1] as $src) {
                    $file_name = basename($src);
                    $file_path = $src;

                    if (is_callable($callback)) {
                        list($file_name, $file_path) = $callback($src);
                    }

                    $file['content'] = str_replace($src, $file_name, $file['content']);
                    $this->addFile($file_name, $file_path);

                }
            }
        }

        return $this;
    }

    public function addContents($file_path, $content, $callback = false)
    {
        if (is_callable($callback))
            $content = $callback($content);

        $this->files[] = [
            'file_path' => $file_path,
            'content'  => $content,
        ];

        return $this;
    }

    public function getFile()
    {
        $this->checkHeaders();
        if (!$this->checkFiles()) {
            throw new Exception('No file was added');
        }

        $contents = implode("\r\n", $this->headers);
        $contents .= "\r\n";
        $contents .= "MIME-Version: 1.0\r\n";
        $contents .= "Content-Type: multipart/related;\r\n";
        $contents .= "\tboundary=\"" . $this->boundary . "\";\r\n";
        $contents .= "\ttype=\"text/html\"\r\n";
        $contents .= "\r\n";
        $contents .= "This is a multi-part message in MIME format.\r\n";
        $contents .= "\r\n";

        foreach ($this->files as $file) {
            $contents .= "--" . $this->boundary . "\r\n";
            $contents .= "Content-Type: " . (mime_content_type($file['file_path'])?:'text/html') . "\r\n";
            $contents .= "Content-Transfer-Encoding: base64\r\n";
            $contents .= "Content-Location: " . $file['file_path'] . "\r\n";
            $contents .= "\r\n";
            $contents .= chunk_split(base64_encode($file['content']), 76);
            $contents .= "\r\n";
        }

        $contents .= "--" . $this->boundary . "--\r\n";

        return $contents;
    }

    private function checkHeaders()
    {
        if (!array_key_exists('date', $this->headers_exists)) {
            $this->setHeader('Date: ', date('D, d M Y H:i:s O', time()));
        }
        if (is_null($this->boundary)) {
            $this->setBoundary();
        }
    }

    private function checkFiles()
    {
        return (count($this->files) == 0) ? false : true;
    }

}
?>
