<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 02/02/17
 * Time: 11:15
 */

namespace ClinicLE\Search\Resource;

class OverrideAccessSearchablePages extends SearchablePages
{
    function hasSearchAccess($page) {
        return true;
    }
}