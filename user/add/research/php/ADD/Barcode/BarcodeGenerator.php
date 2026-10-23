<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 05/07/2019
 * Time: 10:27
 */

namespace ADD\Research\Barcode;
use TCPDF;
class BarcodeGenerator
{
    private $pageHeight = 297;
    private $pageWidth = 210;
    private $xNumPerPage;
    private $yNumPerPage;
    private $margin;

    function __construct(Margin $margin, Label $label, $xgap) {
        $this->xNumPerPage = floor(($this->pageWidth - ($margin->left + $margin->right)) / $label->width);
        $this->yNumPerPage = floor(($this->pageHeight - ($margin->top + $margin->bottom)) / $label->height);
        $this->margin = $margin;
        $this->label = $label;
        $this->xgap = $xgap;

    }

    function generatePdf(IdBatch $idBatch) {
        $pdf = $this->setupPdf();
        $this->generateBarcodes($pdf, $idBatch);
        return $pdf;
    }

    function setUpPdf() {
        $pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('ADD');
        $pdf->SetTitle('ADD barcodes');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setHeaderMargin(0);
        $pdf->setFooterMargin(0);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


        $pdf->SetMargins(0, 0);

        $pdf->setImageScale(1);
        $pdf->SetAutoPageBreak(false);

        return $pdf;
    }

    function generateBarcodes(TCPDF $pdf, IdBatch $idBatch) {
        $codes = $idBatch->nextBatch($this->yNumPerPage);

        $style = array(
            'position' => '',
            'align' => 'L',
            'stretch' => false,
            'fitwidth' => false,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 5,
            'vpadding' => 3,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'courier',
            'fontsize' => 12,
            'stretchtext' => false
        );

        do {
            if ($codes === false) {
                return;
            }
            $currX = $this->margin->left;
            $currY = $this->margin->top;
            $pdf->AddPage();

            $counter = 0;
            for ($y = 0; $y < $this->yNumPerPage; $y++) {
                if (array_key_exists($counter, $codes) === false) {
                    break;
                }
                $curr_code = $codes[$counter];

                for ($x = 0; $x < $this->xNumPerPage; $x++) {
                    $pdf->SetY($currY + $this->label->margin->top + 2);
                    $pdf->SetX($currX + $this->label->margin->left);
                    $pdf->write1DBarcode($curr_code, 'C128', '', '', '', $this->label->barcodeHeight, $this->label->resolution, $style, 'N');
                    #$pdf->setColor('draw',128);
                    #$pdf->Rect($currX, $currY, $this->label->width, $this->label->height);
                    $pdf->setColor('draw',0);
                    $currX = $currX + $this->label->width + $this->xgap;
                }
                $currY = $currY + $this->label->height;
                $currX = $this->margin->left;
                $counter = $counter + 1;
            }
            $codes = $idBatch->nextBatch($this->yNumPerPage);
        } while(true);

    }
}