<?php

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 05/07/2019
 * Time: 10:45
 */
class ADD_Content_LabelPdf implements ADD_ContentInterface
{
    public $pdf;

    function init()
    {
        // TODO: Implement init() method.
    }

    function contentType(&$contentType) {
        $contentType="LabelPdf";
    }
    function __construct($pdf) {
        $this->pdf = $pdf;
    }
    function output() {
        $filename = CLE::sys()->getPath()."/files//example_050.pdf";
        $this->pdf->Output($filename, 'F');
        return file_get_contents($filename);
    }

    function mimeType()
    {
        return "application/pdf";
    }
    function headers() {
        return array();
        return array(
            'Content-Disposition: attachment; filename=research-db-barcodes.pdf'
        );
    }
}
