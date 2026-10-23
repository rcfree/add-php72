<?php
namespace ClinicLE\Client\Container;
use ClinicLE\Client\Container\Cell\AbstractCell;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:14
 */
class Table implements HtmlCreatorInterface
{
    const COUNT_ROWS="count.rows";
    const COUNT_COLUMNS="count.columns";
    private $paged = false;
    private $title;
    private $loader = false;
    private $dataSetName;
    private $dataItemName;
    private $cells = array();
    private $rowConditions=array();
    private $columnConditions=array();
    private $headings=null;
    private $widths;
    private $attrs = array();
    private $tableClass;
    private $countHidden = false;
    private $noCountAsError = false;
    private $countType = self::COUNT_ROWS;

    /**
     * @return string
     */
    public function getCountType()
    {
        return $this->countType;
    }

    /**
     * @param string $countType
     */
    public function setCountType($countType)
    {
        $this->countType = $countType;
    }

    /**
     * @return boolean
     */
    public function isNoHeadings()
    {
        return $this->noHeadings;
    }

    /**
     * @param boolean $noHeadings
     */
    public function setNoHeadings($noHeadings)
    {
        $this->noHeadings = $noHeadings;
    }
    private $noHeadings = false;

    /**
     * @return boolean
     */
    public function isNoCountAsError()
    {
        return $this->noCountAsError;
    }

    /**
     * @param boolean $noCountAsError
     */
    public function setNoCountAsError($noCountAsError)
    {
        $this->noCountAsError = $noCountAsError;
    }

    /**
     * @return boolean
     */
    public function isCountHidden()
    {
        return $this->countHidden;
    }

    /**
     * @param boolean $countHidden
     */
    public function setCountHidden($countHidden)
    {
        $this->countHidden = $countHidden;
    }

    /**
     * @param mixed $tableClass
     */
    public function setTableClass($tableClass)
    {
        $this->tableClass = $tableClass;
    }

    /**
     * @return mixed
     */
    public function getWidths()
    {
        if($this->widths) {
            return $this->widths;
        }

        $widths = array();
        foreach($this->getCells() as $cell) {
            $widths[]=$cell->getWidth();
        }
        return $widths;
    }

    /**
     * @param mixed $widths
     */
    public function setWidths($widths)
    {
        $this->widths = $widths;
    }

    /**
     * @return mixed
     */
    public function getHeadings()
    {
        if($this->headings) {
            return $this->headings;
        }
        $headings = array();
        foreach($this->getCells() as $cell) {
            $headings[]=$cell->getHeading();
        }
        return $headings;
    }

    /**
     * @param mixed $headings
     */
    public function setHeadings($headings)
    {
        $this->headings = $headings;
    }


    /**
     * @return array
     */
    public function getCells()
    {
        return $this->cells;
    }
    
    function __construct($title, $dataSetName, $dataItemName) {
        $this->title = $title;
        $this->dataSetName = $dataSetName;
        $this->dataItemName = $dataItemName;
    }
    function setAttr($attr,$value) {
        $this->attrs[$attr]=$value;
    }
    function createHtml() {
        $html = new \ADD_Content_Html();

        $html->addTextElement("div","",array("ng-show"=>"control.data.gridError","ng-bind-html"=>"control.data.gridError","class"=>"alert alert-error"));
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridWarning","ng-bind-html"=>"control.data.gridWarning","class"=>"alert alert-warning"));

        if($this->isNoCountAsError()) {
            $html->addTextElement("div", "Sorry no " . $this->getTitle() . "s were found matching your search.", array("ng-show" => "control.data.totalRows==0 && !control.data.gridError", "class" => "alert alert-error"));
        }

        if($this->isLoader()) {
            $ajaxLoader = new \ADD_Content_Html();

            $ajaxLoader->startElement("p", array("ng-show" => "control.data.loadingGrid", "class" => "muted"));

            $ajaxLoader->addHtml("Loading &nbsp;");
            $ajaxLoader->addElement("img", array("src" => "/module/clinicle/client/images/ajax-loader.gif"));
            $ajaxLoader->endElement("p");
            $html->addHtml($ajaxLoader);
        }

        if(!$this->isCountHidden()) {
            $html->startElement("div", array("class" => "alert alert-success top-gap", "ng-show" => "control.data
            .gridRows>0"));

            if ($this->isPaged()) {
                $html->addTextElement("span", "{{control.data.firstRow}} - {{control.data.lastRow}} of {{control.data
           .totalRows}} "
                    . $this->getTitle() . "{{ control.data.totalRows == 1 ? '' : 's' }}", array("style" => "margin-right:1em"));
                $html->addTextElement("button", "<i class='icon-backward'></i>&nbsp;Prev", array("class" => "btn btn-small", "type" => "button", "ng-click" => "control.previousPage()", "ng-show" => "control.data.loadingGrid==false && control.data.lessRows"));
                $html->addTextElement("button", "<i class='icon-forward'></i>&nbsp;Next", array("class" => "btn btn-small", "type" => "button", "ng-click" => "control.nextPage()", "ng-show" => "control.data.loadingGrid==false && control.data.moreRows"));
            } else {
                if($this->getCountType()==self::COUNT_ROWS) {
                    $html->addTextElement("span", "{{control.data.gridRows }} " . $this->getTitle() . "{{ control.data.totalRows == 1
                 ? '' : 's' }}");
                }
                else {
                    $html->addTextElement("span", "{{ control.data.gridColumns-1 }} " . $this->getTitle() . "{{ control.data.gridColumns-1 == 1
                 ? '' : 's' }}");
                }
            }


            $html->endElement("div");
            if(!$this->isNoCountAsError()) {
                $html->addTextElement("div", "No " . $this->getTitle() . "s", array("ng-show" => "control.data.gridRows==0 && !control.data.gridError", "class" => "alert alert-success top-gap"));
            }
        }
        $headings = $this->getHeadings();
        $widths=$this->getWidths();

        $attrs = $this->attrs;
        $attrs["ng-show"]="control.data.gridRows>0";

        $helper = new \ADD_Content_Html_TableHelper("",$attrs,$this->tableClass);

        if($this->isNoHeadings()) {
            $helper->setHeadings(null);
        }
        else {
            $helper->setHeadings($headings);
        }
        $helper->setWidths($widths);

        $rowData = array();
        foreach($this->getCells() as $cell) {
            $rowData[]=array($cell->getCell(),$cell->getAttrs());
        }

        $helper->addRow(array(
            "data"=>$rowData,
            "attrs"=>$this->getTableAttrs()
        ));
        $html->startElement("div");
        $html->addHtml($helper->create());
        $html->endElement("div");

        foreach($this->getCells() as $cell) {
            $html->addHtml($cell->afterTable($this));
        }


        return $html;
    }

    function getTableAttrs() {
        $rowAttrs = array("ng-repeat"=>$this->getDataItemName()." in ".$this->getDataSetName());

        foreach($this->rowConditions as $rowCondition) {
            list($field,$op,$value,$class)=explode("|",$rowCondition);
            $rowAttrs["ng-class"]="row.$field.value==$value ? '$class' : ''";
        }
        return $rowAttrs;
    }
    /**
     * @return boolean
     */
    public function isPaged()
    {
        return $this->paged;
    }

    /**
     * @param boolean $paged
     */
    public function setPaged($paged)
    {
        $this->paged = $paged;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return boolean
     */
    public function isLoader()
    {
        return $this->loader;
    }

    /**
     * @param boolean $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return mixed
     */
    public function getDataSetName()
    {
        return $this->dataSetName;
    }

    /**
     * @param mixed $dataSetName
     */
    public function setDataSetName($dataSetName)
    {
        $this->dataSetName = $dataSetName;
    }

    /**
     * @return mixed
     */
    public function getDataItemName()
    {
        return $this->dataItemName;
    }

    /**
     * @param mixed $dataItemName
     */
    public function setDataItemName($dataItemName)
    {
        $this->dataItemName = $dataItemName;
    }

    function addCell(AbstractCell $cell) {
        $cell->init($this);
        $this->cells[]=$cell;
    }

    function setRowConditions($rowConditions) {
        $this->rowConditions=$rowConditions;
    }
    function setColumnConditions($columnConditions) {
        $this->columnConditions=$columnConditions;
    }
}