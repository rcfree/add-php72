<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Migrate;
abstract class AbstractSchemaPopulator {
	abstract function populate(Schema $schemaStructure, SqlParser $sqlParser);
}