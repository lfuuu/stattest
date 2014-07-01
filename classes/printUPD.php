<?php


class printUPD
{

    private static $pageSize = 750;
    private static $defaultRowSize = 29;

    public static function getInfo($positions, $rowSize = null)
    {
        if ($rowSize === null)
        {
            $rowSize = self::$defaultRowSize;
        }

        $page = self::constructPage($positions, $rowSize);

        $size = 0;
        $pageNum = 1;
        foreach($page as $pobj)
        {
            if (defined("print_debug"))
            {
                echo "\n--------------------";
                echo "\n".$pobj["obj"];
                echo "\nsize: ".$size. " (rowSize: ".$rowSize.")";
                echo "\nsize+p[size]: ".($size + $pobj["size"]);
                echo "\npage: ".$pageNum.", pageSize: ".($pageNum*self::$pageSize);
            }

            $isNewPage = ($size + $pobj["size"] >= $pageNum*self::$pageSize);

            if ($isNewPage)
            {
                // if footer or last product line on 2 pages
                if ($pobj["obj"] == "footer" || ($pobj["obj"] == "line" && $pobj["is_last"]))
                {
                    return self::getInfo($positions, $rowSize+1);
                }
                $pageNum++;
            }
            $size += $pobj["size"];
        }

        return array("row_size" => $rowSize, "pages" => $pageNum);
    }

    private static function constructPage($positions, $rowSize)
    {
        $page = array();
        $page[] = array("obj" => "header", "size" => self::$defaultRowSize*10);

        for($i=1 ; $i <= $positions; $i++)
        {
            $page[] = array("obj" => "line", "size" => $rowSize, "is_last" => false);
        }

        if ($positions)
            $page[count($page)-1]["is_last"] = true;

        $page[] = array("obj" => "footer", "size" => self::$defaultRowSize*10);
        
        return $page;
    }
}

