<?php
namespace ClinicLE\Client\Container;
use ClinicLE\Client\Container\Cell\AbstractCell;
use ClinicLE\Client\Container\Legend;
use ClinicLE\Content\Render\HtmlElement;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:14
 */
class LegendTable extends Table implements HtmlCreatorInterface
{
    private $paged = false;
    private $dataSetName;
    private $dataItemName;
    private $cells = array();
    private $rowConditions=array();
    private $columnConditions=array();
    private $headings=null;
    private $widths;
    private $attrs = array();

    /**
     * @return array
     */
    public function getAttrs()
    {
        return $this->attrs;
    }
    private $tableClass;
    private $noHeadings = false;
    private $title;

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

    /** @var  Legend */
    private $legend;

    /**
     * @return \ClinicLE\Client\Container\Legend
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * @param \ClinicLE\Client\Container\Legend $legend
     */
    public function setLegend($legend)
    {
        $this->legend = $legend;
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
        $html = new HtmlElement();

        if($this->getLegend()) {
            $html->addHtml($this->getLegend()->createHtml());
        }
        $attrs = $this->getAttrs();

        $headings = $this->getHeadings();
        $widths=$this->getWidths();

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
            $rowAttrs["ng-class"]=$this->getDataItemName().".$field.value==$value ? '$class' : ''";
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