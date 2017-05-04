<?php

namespace app\widgets\GridViewExport\drivers;

/**
 * @property-read string $name
 * @property-read string $mimeType
 * @property-read string $extension
 * @property-read string $icon
 */
interface ExportDriver
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param int $key
     * @param array $columns
     */
    public function createHeader($key, $columns = []);

    /**
     * @param int $key
     * @param array $rows
     */
    public function setData($key, $rows = []);

    /**
     * @param int $key
     * @param bool|true $deleteAfter
     */
    public function fetchFile($key, $deleteAfter = true);

}