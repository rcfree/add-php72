<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/19
 * Time: 11:51
 */
use ADD\Research\Barcode\IdBatch;
use ADD\Research\Barcode\Margin;
use ADD\Research\Barcode\Label;
use ADD\Research\Barcode\BarcodeGenerator;

class ADD_Component_LabelOutput extends ADD_Component
{
    function contentType(&$contentType) {
        $contentType="LabelPdf";
    }
    function menuPath(&$menuPath) {
        $menuPath="research/barcode-output";
    }
    function output($values = array()) {
        CLE::info("Print labels");
        CLE::web()->addWarning("Print labels");
        $numLabels = CLE::sys()->getParam('count') * 9;

        $idGen = new ADD_Tool_IdGenerator();

        $codes = $idGen->generateIds($numLabels);
        CLE::info("codes:",$codes);
        $batch = new IdBatch($codes);
        $margin = new Margin(4,17,4,13);
        $label = new Label(48.9,29.6,25,0.3, new Margin(1,1,1,1));
        $generator = new BarcodeGenerator($margin, $label, 1.5);
        $pdf = $generator->generatePdf($batch);
        $content = new \ADD_Content_LabelPdf($pdf);
        return $content;
    }
}