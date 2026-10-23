<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 *
 * @author rcf8
 */
namespace ClinicLE\Command;
use ClinicLE\Common\Config\AbstractConfig;

interface CommandInterface {
    function getOptsList();
    function runCommand(AbstractConfig $options);
    function getDescription();
}
