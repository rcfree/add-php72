<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 05/07/2019
 * Time: 10:26
 */

namespace ADD\Research\Barcode;


class Margin
{
    public $left;
    public $top;
    public $right;
    public $bottom;

    function __construct($left,$top,$right,$bottom) {
        $this->left = $left;
        $this->top = $top;
        $this->right = $right;
        $this->bottom = $bottom;
    }

}