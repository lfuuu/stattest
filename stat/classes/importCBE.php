<?php


class importCBE
{
    private $helper = null;
    private $filePrefix = "";
    private $mainList = null;

    public function __construct($prefix, $filePath)
    {
        $this->filePrefix = $prefix;

        $this->mainList = new parserCBE($filePath);

        $this->helper   = new helperCBE();
        $this->helper0  = new helperCBE($this->mainList);

        $a = $this->helper0->getByDays();

        foreach($a as $day => $devNull)
        {
            $o = new parserCBE($this->getFilePath($day));
            $this->helper->combineCBE($o);
        }
        
        $this->helper->combineCBE($this->mainList, true);
    }

    private function getFilePath($day)
    {
        return PAYMENTS_FILES_PATH.$this->filePrefix."__".$day.".txt";
    }

    public function save()
    {
        $header = $this->mainList->getHeader();
        foreach($this->helper->getByDays() as $day => $dayData)
        {
            $builder = new builderCBE($header, $dayData);
            $builder->buildAndSave($this->getFilePath($day));
        }
        
        return $this->helper->info->getInfo();
    }
}

class parserCBE
{
    public $header = [];
    public $body = [];

    public function __construct($f)
    {
        if (!file_exists($f))
            return;

        $f = file_get_contents($f);

        if (strpos($f, "1CClientBankExchange") === false)
            throw new Exception("Неизвестный формат");


        $f = iconv("cp1251", "utf8", $f);

        $f = explode("\r\n", $f);

        $state = 0;
        $header = $body = $bodyMain = [];
        foreach($f as $l)
        {
            $l = trim($l);

            if (!$l)
                continue;

            if ($state == 0)
            {
                if (strpos("СекцияРасчСчет", $l) !== false)
                {
                    $state = 1;
                }
            } else if ($state == 1)
            {
                if (strpos("КонецРасчСчет", $l) !== false)
                {
                    $state = 3;
                } else {
                    if ($l)
                    {
                        $this->explodeLine($header, $l);
                    }
                }
            } else if ($state == 3)
            {
                if (strpos($l, "СекцияДокумент") !== false)
                {
                    $state = 4;
                    $this->explodeLine($body, $l);
                }
            } else if ($state == 4)
            {
                if (strpos($l, "КонецДокумента") !== false)
                {
                    if ($body)
                    {
                        $this->explodeSection($bodyMain, $body);
                        $body = [];
                    }
                    $state = 3;
                } else {
                    $this->explodeLine($body, $l);
                }
            }
        }

        $this->header = $header;
        $this->body = $bodyMain;
    }

    private function explodeLine(&$b, &$l)
    {
        $pos = strpos($l, "=");

        $start = substr($l, 0, $pos);
        $end = substr($l, $pos+1);
        $b[$start] = $end;
    }

    private function explodeSection(&$bb, &$b)
    {
        $k ="";
        foreach(["Дата", "Номер", "Сумма", "Плательщик", "ПлательщикИНН"] as $f)
        {
            $k .= ($k ? "--" : "").$b[$f];
        }
        if (isset($bb[$k])) throw new Exception("Платеж может задублироваться");
        $bb[$k] = $b;
    }


    public function getHeader()
    {
        return $this->header;
    }

    public function getBody()
    {
        return $this->body;
    }
}

class helperCBE
{
    private $body = [];
    private $header = [];
    public $info = null;

    public function __construct(parserCBE $o = null)
    {
        if ($o !== null)
        {
            $this->header = $o->getHeader();
            $this->body = $o->getBody();
        }
        $this->info = new infoCBE();
    }

    public function getByDays()
    {
        $days = [];

        foreach($this->body as $l)
        {
            $date = date("d-m-Y", $this->detectDate($l));

            if (!isset($days[$date]))
                $days[$date] = [];

            $days[$date][] = $l;
        }

        return $days;
    }

    private function detectDate($l)
    {
        $day = null;
        if (isset($l["ДатаСписано"]) && $l["ДатаСписано"])
        {
            $day = strtotime($l["ДатаСписано"]);
        }

        if (isset($l["ДатаПоступило"]) && $l["ДатаПоступило"])
        {
            $day = strtotime($l["ДатаПоступило"]);
        }


        if (!$day)
        {
            throw new Exception("Дата платежа не определенна");
        }

        return $day;
    }

    public function combineCBE(parserCBE $o, $isInfo = false)
    {
        foreach($o->getBody() as $k => $l)
        {
            $day = date("d-m-Y", $this->detectDate($l));

            if ($isInfo)
            {
                $section = (isset($this->body[$k]) ? "skiped" : "new");
                $this->info->increase($day, $section);
                $this->info->increase($day, "all");

                $this->info->increase($day, "sum_plus", ($l["ДатаСписано"] ? 0 : $l["Сумма"]));
                $this->info->increase($day, "sum_minus", ($l["ДатаСписано"] ? $l["Сумма"] : 0));
            }

            if (!isset($this->body[$k]))
            {
                $this->body[$k] = $l;
            }
        }
    }
}

class infoCBE
{
    private $data = [];

    public function setValue($day, $type, $value)
    {
        if (!isset($this->data[$day])) $this->data[$day] = [];

        $this->data[$day][$type] = $value;
    }

    public function increase($day, $type, $value = 1)
    {
        if (!isset($this->data[$day])) $this->data[$day] = [];
        if (!isset($this->data[$day][$type])) $this->data[$day][$type] = 0;

        $this->data[$day][$type] += $value;
    }

    public function getInfo()
    {
        ksort($this->data);
        return $this->data;
    }
}

class builderCBE
{
    private $header = [];
    private $body = [];

    public function __construct($header, $body)
    {
        $this->header = $header;
        $this->body = $body;
    }

    public function buildAndSave($filePath)
    {
        $str = $this->getHeaderString();
        $str .= "\r\n". $this->getBodyString();

        file_put_contents($filePath, iconv("utf-8", "cp1251", $str));
    }

    private function getHeaderString()
    {
        $str = "1CClientBankExchange\r\nВерсияФормата=1.02\r\nКодировка=Cp1251\r\nОтправитель=\r\nПолучатель=\r\n";
        $str .= "СекцияРасчСчет";
        foreach($this->header as $k => $l)
        {
            $str .= "\r\n".$k."=".$l;
        }
        $str .= "\r\n"."КонецРасчСчет";

        return $str;
    }

    private function getBodyString()
    {
        $str = "";
        foreach($this->body as $section)
        {
            foreach($section as $k => $l)
            {
                $str .= ($str ? "\r\n" : "") . $k."=".$l;
            }
            $str .= "\r\n" . "КонецДокумента";
        }

        return $str;
    }
}











