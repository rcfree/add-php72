<?php
namespace ADD\ResearchSearch\Util;
use ClinicLE\Client\Container\Table;
use ClinicLE\Client\Container\Cell\GotoPageFieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\FieldCell;
use ADD\ResearchSearch\StudyActionCell;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 20/09/16
 * Time: 16:03
 */
class ResearchSearchTable
{
    public function createTable($html) {
        $table = new Table("recruitment record","control.data.grid","row");
        $table->setPaged(true);
        $table->setLoader(true);
        $table->addCell(new GotoPageCell("Participant Name","{{row.first_name}} {{row.last_name}}","'/?pid=' + row.patient_id"));
        $table->addCell(new FieldCell("Date of Birth","date_of_birth"));
        $table->addCell(new FieldCell("S number","identifier"));
        $table->addCell(new GotoPageFieldCell("Recruit status","current_status","'research/contact/details?pid=' + row.patient_id + '&eid=' + row.contact_id"));
        $table->addCell(new FieldCell("ILH number","ilh_identifier"));
        $table->addCell(new FieldCell("Study","study"));
        $table->addCell(new FieldCell("Study no.","study_no"));
        $table->addCell(new GotoPageFieldCell("Study status","study_status","'research/study/ssummary?pid=' + row.patient_id + '&eid= ' + row.study_id"));
        $table->addCell(new StudyActionCell("Available actions"));

        $html->addHtml($table->createHtml());
    }
}