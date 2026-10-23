<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Page;
use ClinicLE\Page\AbstractPage;
/**
 * Description of PageContainerInterface
 *
 * @author rcf8
 */
interface PageContainerInterface {
	public function getPage();
	public function setPage(AbstractPage $page);
}
