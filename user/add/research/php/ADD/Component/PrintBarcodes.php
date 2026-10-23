<?php



class ADD_Component_PrintBarcodes extends ADD_Component_Form_File {
    public function getType() {
        return "Form";
    }
    public function getFormMethod() {
        return "GET";
    }
	protected $_componentList = array('OptionContainer', 'SaveButton', 'RequiredFields_Validate');
    protected $_formAction = "/research/barcode-output";
    public function title(&$title) {
        $title="Print barcodes";
    }
    
    public function breadcrumb(&$breadcrumb) {
        $breadcrumb[]="Research";    
        $breadcrumb[]="Print barcodes";
    }
	
    function menuPath(&$menuPath) {
        $menuPath = "research/barcodes";
    }

    public function expectedPermissions(&$expPerms) {
        $expPerms = array("view");
    }

    function setup() {
        $this->setIgnoreNg(true);
        parent::setup();
    }
    function beforeAllGenerate() {
        $page = $this->_page;

        $method = $this->getFormMethod();
        $action = $this->getFormAction();
        $attrs = array("method"=>$method,"id"=>"content-form","onsubmit"=>"return true", "target"=>"_blank"); //ADD.Form
        //.isValid()
        //;");
        if($action) $attrs['action']=$action;
        $page->html()->startElement("form",$attrs,"form_start");
        $page->html()->addElement("input",array("value"=>$page->getModule()->getAlias().":".$page->getName(),"name"=>"__pim","type"=>"hidden"));
    }
}

?>
