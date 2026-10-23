<?php
interface ADD_Pathology_DataSourceInterface {
	public function nextTestItem();
	public function getTestItemTotal();
}