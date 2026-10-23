<?php

class ADD_Client_Util_MatrixConvert {
	private $xField;
	private $yField;
	private $valueField;
	
	function __construct($xField,$yField,$valueField) {
		$this->xField = $xField;
		$this->yField = $yField;
		$this->valueField = $valueField;
	}
	
	function convertDataToXKeyed(array $flatData, $singleValues=false) {
		$xKeyedData=array();
		$xHeadings = array();
		$yHeadings = array();
		
		$warnings = array();
		foreach ($flatData as $flatItem) {
			$xData = ADD_Util::val($this->xField,$flatItem);
			$yData = ADD_Util::val($this->yField,$flatItem);
			
			if($singleValues) {
                $xValue = $xData;
                $yValue = $yData;

                $xLabel = $xData;
                $yLabel = $yData;
            }
            else {
                $xValue = $xData['value'];
                $yValue = $yData['value'];

                $xLabel = $xData['label'];
                $yLabel = $yData['label'];
            }

			if (!isset($xKeyedData[$xValue])) {
				$xKeyedData[$xValue]=array(); 
				$xHeadings[$xValue]=$xLabel;
			}
			if (array_search($yValue,array_keys($yHeadings))===false) {
				$yHeadings[$yValue]=$yLabel;
			}
			$value = ADD_Util::val($this->valueField,$flatItem);
			if(isset($xKeyedData[$xValue][$yValue])) {
                //::warn("value",$xKeyedData[$xValue][$yValue]);

				$warnings[]=$xValue." / ".$yValue." = ".(is_array($value) ? var_export($value,true) : $value);
			}
			$xKeyedData[$xValue][$yValue]=array("value"=>htmlspecialchars($value["value"]), "x"=>$xValue, "y"=>$yValue, "data"=>$flatItem);
		}
		if(count($warnings)>0) {
			ADD::web()->addWarning("Data points present with same column/row:<ul><li>".implode("</li><li>",$warnings)."</li></ul>");
		}
		return array($xKeyedData, $xHeadings, $yHeadings);
	}
	
	function process(array $flatData) {
		$matrixData=array();
		
		list($xKeyedData, $xHeadings, $yHeadings) = $this->convertDataToXKeyed($flatData);
		
		$yPos=0;
		foreach($yHeadings as $yKey => $yLabel) {
			$row=array(array("label"=>$yLabel,"head"=>true, "value"=>$yKey));	
			
			foreach($xHeadings as $xKey => $xLabel) {
				$row[] = ADD_Util::val($yKey,$xKeyedData[$xKey]," ");
			}
			
			$matrixData[]=$row;
			$yPos++;
		}
		if(count($matrixData)>=1) {
			array_unshift($matrixData,array_merge(array(array("head"=>true,"label"=>"")),array_map(function($key) use($xHeadings) { return array("label"=>$xHeadings[$key],"head"=>true,"value"=>$key, "link_column"=>true); },array_keys($xHeadings))));
		}
		return $matrixData;
	}
}