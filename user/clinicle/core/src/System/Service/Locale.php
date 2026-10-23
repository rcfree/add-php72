<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\System\Service;
use DateTime;
use DateTimeZone;
class Locale {
    private $displayDate="d/m/Y";
    private $displayTime="d/m/Y H:i:s";
    function nowAsISO() {
        return date("Y-m-d H:i:s");
    }
    function todayAsISO() {
        return date("Y-m-d");
    }
    static function age($startDate,$endDate=null) {
        $endDate = DateTime::createFromFormat('d/m/Y',$endDate);
        $startDate = DateTime::createFromFormat('d/m/Y',$startDate);
        if (!$endDate) return null;
                if (!$startDate) return null;
        $years = $endDate->format('Y') - $startDate->format('Y');
        //echo "month:".$startDate->format('m')." - ". $endDate->format('m')."\n";
        if ($endDate->format('m') < $startDate->format('m') || 
            $endDate->format('m') == $startDate->format('m') && $endDate->diff($startDate)->d>0) {
            $years--;
        }
        //echo "years:$years\n";
        return $years;
    }
    function date($format) {
        return date($format);
    }
    function isoToDisplayDate($date)
    {
        $dt = $date ? DateTime::createFromFormat("Y-m-d H:i:s", $date, new DateTimeZone(date_default_timezone_get()))
            : null;
        if (!$dt) {
            return null;
        }
        return $dt->format($this->displayDate);
    }
    function epoch() {
        return \time();
    }

    function displayDateToISO($date) {
        $dt = DateTime::createFromFormat($this->displayDate,$date);
        if(!$dt) {
            return "";
        }
        return $dt->format("Y-m-d");
    }

}