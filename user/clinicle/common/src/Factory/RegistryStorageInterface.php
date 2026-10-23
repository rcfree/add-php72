<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 30/09/2016
 * Time: 12:38
 */

namespace ClinicLE\Common\Factory;


interface RegistryStorageInterface
{
    function createRegistry();
    function storeRegistry(FactoryRegistry $registry);
}