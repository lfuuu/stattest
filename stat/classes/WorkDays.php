<?php

class WorkDays
{
    public function isWorkDayFromMonthStart($timestamp, $number = 3)
    {
        $date = self::checkTimestamp($timestamp);
        $number = self::checkNumber($number, $date);
        return self::isWorkDayCheckCount($date, $number);
    }
    public function isWorkDayFromMonthEnd($timestamp, $number = 3)
    {
        $date = self::checkTimestamp($timestamp);
        $number = self::checkNumber($number, $date);
        return self::isWorkDayCheckCount($date, $number, false);
    }
    private function isWorkDayCheckCount(DateTime $date, $number = 3, $from_start_month = true) 
    {
        $one_day = new DateInterval('P1D');
        $work_day = self::isWorkDay($date);
        if ($work_day) 
        {
            $count_work_days = 1;
            $month = $date->format('n');
            if ($from_start_month) 
            {
                $date->sub($one_day);
            } else {
                $date->add($one_day);
            }
            while ($date->format('n') == $month) 
            {
                $work_day = self::isWorkDay($date);
                if ($work_day) 
                {
                    $count_work_days++;
                    if ($count_work_days > $number) 
                    {
                        return false;
                    }
                }
                if ($from_start_month) 
                {
                    $date->sub($one_day);
                } else {
                    $date->add($one_day);
                }
            }
            if ($count_work_days == $number) 
            {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
    private function checkTimestamp($timestamp) 
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);

        if (!is_object($date)) 
        {
            $date = new DateTime();
        }
        return $date;
    }
    private function checkNumber($number, DateTime $date) {
        if (!preg_match("|^[\d]+$|", $number) || $number < 0 || $number > $date->format('t'))
        {
            return false;
        }
        return $number;
    }
    private function isWorkDay(DateTime $date) 
    {
        $day = $date->format('j');
        $month = $date->format('n');
        $year = $date->format('Y');

        $holidays = array(
                '2014' => array(
                    '1' => array(
                        '1' => 1, 
                        '2' => 1, 
                        '3' => 1, 
                        '4' => 1, 
                        '5' => 1, 
                        '6' => 1, 
                        '7' => 1, 
                        '8' => 1,
                        ),
                    '2' => array(
                        '23' => 1,
                        ),
                    '3' => array(
                        '8' => 1,
                        '9' => 1,
                        '10' => 1,
                        ),
                    '5' => array(
                        '1' => 1,
                        '2' => 1, 
                        '3' => 1,
                        '4' => 1,
                        '9' => 1, 
                        '10' => 1, 
                        '11' => 1, 
                        ),
                    '6' => array(
                            '12' => 1,
                            '13' => 1, 
                            '14' => 1,
                            '15' => 1,
                            ),
                    '11' => array(
                            '3' => 1,
                            '4' => 1,
                            ),
                    ),
                    '2015'=> array(
                            '1' => array(
                                '1' => 1, 
                                '2' => 1, 
                                '3' => 1, 
                                '4' => 1, 
                                '5' => 1, 
                                '6' => 1, 
                                '7' => 1, 
                                '8' => 1,
                                '9' => 1,
                                '10' => 1,
                                '11' => 1,
                                ),
                            '2' => array(
                                '23' => 1,
                                ),
                            '3' => array(
                                '8' => 1,
                                '9' => 1,
                                ),
                            '5' => array(
                                    '1' => 1,
                                    '2' => 1, 
                                    '3' => 1,
                                    '4' => 1,
                                    '5' => 1,
                                    '9' => 1, 
                                    '10' => 1, 
                                    '11' => 1, 
                                    ),
                            '6' => array(
                                    '12' => 1,
                                    '13' => 1, 
                                    '14' => 1,
                                    ),
                            '11' => array(
                                    '4' => 1,
                                    ),
                            ),
                            );
        if (isset($holidays[$year][$month][$day])) 
        {
            return false;
        }
        $not_holidays = array(
                '2014' => array(
                    '1' => array(),
                    '2' => array(),
                    '3' => array(),
                    '4' => array(),
                    '5' => array(),
                    '6' => array(),
                    '7' => array(),
                    '8' => array(),
                    '9' => array(),
                    '10' => array(),
                    '11' => array(),
                    '12' => array(),
                    ),
                '2015'=> array(
                    '1' => array(),
                    '2' => array(),
                    '3' => array(),
                    '4' => array(),
                    '5' => array(),
                    '6' => array(),
                    '7' => array(),
                    '8' => array(),
                    '9' => array(),
                    '10' => array(),
                    '11' => array(),
                    '12' => array(),
                    ),
                );
        if (isset($not_holidays[$year][$month][$day])) 
        {
            return true;
        }
        return in_array($date->format('N'), array(1, 2, 3, 4, 5));
    }
}
