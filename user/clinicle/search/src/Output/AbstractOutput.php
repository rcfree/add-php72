<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 12:02
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Util\ParamSelectStatement;

abstract class AbstractOutput
{
    private $runner;

    /**
     * @return QueryRunner
     */
    public function getRunner()
    {
        return $this->runner;
    }


    function __construct(QueryRunner $runner) {
        $this->runner = $runner;
    }

    abstract function outputResponse();

}