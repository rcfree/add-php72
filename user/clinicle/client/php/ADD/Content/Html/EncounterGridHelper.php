<?php

class ADD_Content_Html_EncounterGridHelper {
	private $_title;
	private $_appId;
	private $_filters = array();
	private $_beforeRowActions = array();
	private $_afterRowActions = array();
	private $_beforeColumnActions = array();
	private $_afterColumnActions = array();
	private $_attrs = array();
	private $_tableClass;
	private $_rowConditions=array();
	private $_withinGrid;
	private $_properties = array();
	private $_loadingHidden=false;
	private $_headings = array();
	private $noCount=false;
	private $widths=array();
	private $dataPaged = false;

    /**
     * @return boolean
     */
    public function isDataPaged()
    {
        return $this->dataPaged;
    }

    /**
     * @param boolean $dataPaged
     */
    public function setDataPaged($dataPaged)
    {
        $this->dataPaged = $dataPaged;
    }

	protected $_angularController = "EncounterGridController";
	function __construct($appId, $title, $attrs=array(),$tableClass=null) {
		$this->_title = $title;
		$this->_appId = $appId;
		$this->_attrs = $attrs;
		$this->_tableClass = $tableClass;
	}
	
	function putProperty($name,$value) {
		$this->_properties[$name]=$value;
	}
	function setProperties($properties) {
		$this->_properties = $properties;
	}
	function setHeadings(array $headings) {
		$this->_headings = $headings;
	}
	function setNoCount($noCount) {
		$this->noCount = $noCount;
	}
	function hasNoCount() {
		return $this->noCount;
	}
	function setRepeatRow($repeatRow) {
		$this->_repeatRow = $repeatRow;
	}
	function addFilter($filterField,$filterOp="eq",$filterValues) {
		$this->_filters[]=$filterField."|".$filterOp."|".$filterValues;
	}
	function setRowConditions($rowConditions) {
		$this->_rowConditions=$rowConditions;
	}
	function setColumnConditions($columnConditions) {
		$this->_columnConditions=$columnConditions;
	}
	function addActionAfterRow(ADD_Client_Action_GridRowActionInterface $gridRowAction) {
		$this->_afterRowActions[]=$gridRowAction;
	}
	
	function addActionBeforeRow(ADD_Client_Action_GridRowActionInterface $gridRowAction) {
		$this->_beforeRowActions[]=$gridRowAction;
	}
	
	function addActionAfterColumn(ADD_Client_Action_GridColumnActionInterface $gridColumnAction) {
		$this->_afterColumnActions[]=$gridColumnAction;
	}
	
	function addActionBeforeColumn(ADD_Client_Action_GridRowActionInterface $gridColumnAction) {
		$this->_beforeColumnActions[]=$gridColumnAction;
	}
	
	function setAngularController($angularController) {
		$this->_angularController = $angularController;
	}
	function addHtmlWithinGrid($withinGrid) {
		$this->_withinGrid=$withinGrid;
	}
	function setLoadingHidden($loadingHidden) {
		$this->_loadingHidden = $loadingHidden;
	}
	function isLoadingHidden() {
		return $this->_loadingHidden;
	}
	function setWidths($widths) {
		$this->widths = $widths;
	}
	function create($page) {
		$grid = new ADD_Content_Html();
		
		$attrs = array("id"=>$this->_appId,"ng-controller"=>$this->_angularController,"ng-cloak"=>"","style"=>"overflow-x:auto");
		foreach($this->_properties as $name => $value) {
			$attrs['cle-'.$name]=$value;
		}
		$attrs = array_merge($attrs,$this->_attrs);
		
		$grid->startElement("div",$attrs);


		
		$grid->addTextElement("label","No ".$this->_title."s",array("ng-show"=>"loadingGrid==false && gridRows==0","ng-cloak"=>""));
		
		$title = $this->_title;
		if(!$this->hasNoCount()) {
			$title.="{{ gridRows == 1 ? '' : 's' }}";
		}

		if($this->isDataPaged()) {
            $grid->addTextElement("label","{{firstRow}} - {{lastRow}} of {{totalRows}} ".$title,array("ng-show"=>"gridRows>0","ng-cloak"=>""));

		}
        else {
            $grid->addTextElement("label", ($this->hasNoCount() ? "" : "{{gridRows}} ").$title, array("ng-show" => "gridRows>0", "ng-cloak" => ""));
        }
		if(!$this->isLoadingHidden()) {
			$ajaxLoader = new ADD_Content_Html();
			$ajaxLoader->startElement("p",array("ng-show"=>"loadingGrid==true","class"=>"muted overlay"));
			$ajaxLoader->addHtml("Loading..&nbsp;");
			$ajaxLoader->addElement("img",array("src"=>"/module/clinicle/client/images/ajax-loader.gif"));
			$ajaxLoader->endElement("p");
			$grid->addHtml($ajaxLoader);
			$grid->addTextElement("div","&nbsp;",array("ng-show"=>"(gridRows==0 || !gridRows) && loadingGrid==true","style"=>"height:38px"));
		}
        $grid->addTextElement("div","",array("ng-bind-html"=>"gridError","ng-show"=>"gridError","ng-cloak"=>"","class"=>"alert alert-error"));
        $grid->addTextElement("div","",array("ng-bind-html"=>"gridWarning","ng-show"=>"gridWarning","ng-cloak"=>"","class"=>"alert alert-warning"));
        $grid->addTextElement("div","",array("ng-bind-html"=>"gridMessage","ng-show"=>"gridMessage","ng-cloak"=>"","class"=>"alert alert-success"));

        $tableAttrs = array("ng-show"=>"gridRows>0");
		if(!$this->widths) {
			$tableAttrs['style']="width:100%";
		}
			
		$table = new ADD_Content_Html_TableHelper("",$tableAttrs,$this->_tableClass);
		$repeatRow=array();
		$headings = array();
		foreach($this->_beforeRowActions as $beforeAction) {
			if(!$beforeAction->isAvailable()) continue;
			$repeatRow[]=$beforeAction->outputMarkup();
			$headings[]=$beforeAction->getColumnHeading();
		}
		$repeatRow = array_merge($repeatRow,$this->_repeatRow);
		
		$headings = array_merge($headings,$this->_headings);
		foreach($this->_afterRowActions as $endAction) {
			if(!$endAction->isAvailable()) continue;
			$repeatRow[]=$endAction->outputMarkup();
			$headings[]=$endAction->getColumnHeading();
		}
		$table->setHeadings($headings);

		$table->setWidths($this->widths);
		
		$rowAttrs = array("ng-repeat"=>"row in grid");
		
		
		foreach($this->_rowConditions as $rowCondition) {
			list($field,$op,$value,$class)=explode("|",$rowCondition);
			$rowAttrs["ng-class"]="row.$field.value==$value ? '$class' : ''";
		}
		$tableData = array("data"=>$repeatRow,'attrs'=>$rowAttrs);
		
		$table->addRow($tableData);	
		foreach($this->_afterColumnActions as $endAction) {
			if(!$endAction->isAvailable()) continue;
			$table->addRow(array("data"=>
				array(array($endAction->outputMarkup(),array("ng-repeat"=>"cell in range(1,gridColumns)")
			))));
		}
        $grid->startElement("div",array("class"=>"field-indent"));
        if($this->isDataPaged()) {
            $grid->startElement("div",array("style"=>"margin-bottom:4px;display:block"));
            $grid->addTextElement("button","<i class='icon-backward'></i>&nbsp;Prev",array("class"=>"btn","type"=>"button","ng-click"=>"previousPage()","ng-show"=>"loadingGrid==false && lessRows"));
            $grid->addTextElement("button","<i class='icon-forward'></i>&nbsp;Next",array("class"=>"btn","type"=>"button","ng-click"=>"nextPage()","ng-show"=>"loadingGrid==false && moreRows"));

            $grid->addTextElement("button","<i class='icon-refresh'></i>&nbspRefresh..",array("class"=>"btn","type"=>"button","ng-click"=>"refresh()","ng-show"=>"loadingGrid==false && gridRows>0"));
            $grid->endElement("div");
        }
		$grid->addHtml($table->create());
        $grid->addTextElement("p","No data in table",array("ng-cloak"=>"","class"=>"muted","ng-show"=>"loadingGrid==false && gridRows==0"));
		$grid->endElement("div");
		$grid->addHtml($this->afterCreate());
		if($this->_withinGrid) {
			$grid->addHtml($this->_withinGrid);
		}
		$grid->endElement("div");
		
		return $grid;
	}

	function afterCreate() {
		$html = new ADD_Content_Html();
		foreach($this->_beforeRowActions as $beforeAction) {
			if(!$beforeAction->isAvailable()) continue;
			$beforeAction->afterOutputMarkup($html);
		}
		foreach($this->_afterRowActions as $endAction) {
			if(!$endAction->isAvailable()) continue;
			$endAction->afterOutputMarkup($html);
		}
		return $html;
	}
}
