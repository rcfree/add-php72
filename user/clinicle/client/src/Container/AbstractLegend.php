<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 10/10/17
 * Time: 20:33
 */

namespace ClinicLE\Client\Container;


use ClinicLE\Content\Render\HtmlElement;

abstract class AbstractLegend implements HtmlCreatorInterface
{
    const COUNT_ROWS="count.rows";
    const COUNT_COLUMNS="count.columns";

    private $countHidden = false;
    private $noCountAsError = false;
    private $countType = self::COUNT_ROWS;
    private $loader = false;

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
}