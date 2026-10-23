<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 05/07/2019
 * Time: 10:26
 */

namespace ADD\Research\Barcode;


class Label {
    public $width;
    public $height;
    public $barcodeHeight;
    public $resolution;
    public $margin;

    function __construct($width, $height, $barcodeHeight, $resolution, Margin $margin) {
        $this->width = $width;
        $this->height = $height;
        $this->barcodeHeight = $barcodeHeight;
        $this->resolution = $resolution;
        $this->margin = $margin;
    }
}