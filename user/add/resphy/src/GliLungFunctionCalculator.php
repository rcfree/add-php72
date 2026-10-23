<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 28/05/19
 * Time: 12:07
 */
namespace ADD\Resphy;
use ClinicLE\Common\Exception\NullValueException;

class GliLungFunctionCalculator
{
    const PREFIX="LungFunction.pred";
    const FEV1="FEV1";
    const FVC="FVC";
    const FEV1_FVC="FEV1FVC";

    private $genderIndex;
    private $noGender = false;
    private $height;
    private $age;
    private $precision = 2;
    private $rPackagePath;
    private $pathToRBinary;

    function __construct($gender, $height, $age, $ethnicity, $rPackagePath, $pathtoRBinary = "/usr/bin/R") {
        switch($gender) {
            case "M":
                $this->genderIndex = 1;
                break;
            case "F":
                $this->genderIndex = 2;
                break;
            default:
                $this->noGender = true;
        }
        if ($height === null) {
            throw new \InvalidArgumentException("height is null");
        }
        $this->height = $height / 100;
        if ($age === null) {
            throw new \InvalidArgumentException("age is null");
        }
        $this->age = $age;
        if ($ethnicity === null) {
            throw new \InvalidArgumentException("ethnicity is null");
        }
        $this->ethnicity = $ethnicity;
        $this->rPackagePath = $rPackagePath;
        $this->pathToRBinary = $pathtoRBinary;
    }

    function calculatePredValues() {
        if ($this->noGender === true) {
            return array(
                self::PREFIX."_fev1" => "",
                self::PREFIX."_fvc" => "",
                self::PREFIX."_fev1_fvc" => "",
                self::PREFIX."_pef" => ""
            );
        }
        $data = array(
            self::PREFIX."_fev1" => $this->calculateSinglePredValue(self::FEV1),
            self::PREFIX."_fvc" => $this->calculateSinglePredValue(self::FVC),
            self::PREFIX."_fev1_fvc" => $this->calculateSinglePredValue(self::FEV1_FVC),
            self::PREFIX."_pef" => ""
        );
        return $data;
    }

    private function calculateSinglePredValue($type) {

        $codeToRun = array("library(rspiro, lib.loc = c('$this->rPackagePath', '/usr/lib/R/library'))");

        $codeToRun[] = "pred_GLI({$this->age},{$this->height},{$this->genderIndex}, {$this->ethnicity},'{$type}')";

        $cmd = $this->pathToRBinary. " -e \"". implode(";",$codeToRun). "\" --slave  2>&1";
        $output = \ADD_Tool_Command::executeWithException($cmd);
        $singleValue = "";
        $errors = $this->extractErrors($output);
        if (count($errors)>0) {
            throw new GliLungFunctionException(implode("\n",$errors));
        }
        foreach($output as $line) {
            $tmp = explode("]",$line);
            if (count($tmp)==2) {
                $singleValue = trim($tmp[1]);
                break;
            }
        }

        return $singleValue ? number_format(round((float)$singleValue, $this->precision),$this->precision) : "";

    }

    private function extractErrors($output) {
        $nextIsError = false;
        $errors = array();
        foreach($output as $line) {
            if ($nextIsError === true) {
                $errors[]=$line;
                $nextIsError = false;
                continue;
            }
            if (preg_match("/^Warning message/",$line)) {
                $nextIsError = true;
            }
        }
        return $errors;
    }
}