<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 01/11/17
 * Time: 20:07
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\System\Service\CoreServices;

abstract class AbstractFileOutput extends AbstractOutput
{
    private $definitionPath;
    private $exportPath;
    private $searchId;

    /**
     * @return mixed
     */
    public function getSearchId()
    {
        return $this->searchId;
    }

    /**
     * @param mixed $searchId
     */
    public function setSearchId($searchId)
    {
        $this->searchId = $searchId;
    }

    /**
     * @return mixed
     */
    public function getDefinitionPath()
    {
        return $this->definitionPath;
    }

    /**
     * @param mixed $definitionPath
     */
    public function setDefinitionPath($definitionPath)
    {
        $this->definitionPath = $definitionPath;
    }

    /**
     * @return mixed
     */
    public function getExportPath()
    {
        return $this->exportPath;
    }

    /**
     * @param mixed $exportPath
     */
    public function setExportPath($exportPath)
    {
        $this->exportPath = $exportPath;
    }

    function createDefinitionPath() {
        if(!$this->getDefinitionPath()) {
            throw new NullValueException("definitionPath is not set");
        }
        $definitionPath = $this->getDefinitionPath()."/".$this->getSearchId();
        if(!is_dir($definitionPath)) {
            \CLE::info("Making directory: ".$definitionPath);

            \CLE::svc(CoreServices::FILESYSTEM)->mkdir($definitionPath);
        }
        return $definitionPath;
    }
}