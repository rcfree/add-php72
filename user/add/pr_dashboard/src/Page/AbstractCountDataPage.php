<?php

namespace ADD\PR\Page;
use ADD\PR\SearchConfig;
use ADD\PR\SearchContent;
use ADD\PR\Util\FilterSearchContent;
use ClinicLE\Common\Config\FileConfig;
use ClinicLE\Search\Output\StandardPagedOutput;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use CLE;
use ClinicLE\Search\Resource\SearchServices;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
abstract class AbstractCountDataPage extends \ClinicLE\Search\Page\AbstractSearchPage
{

}