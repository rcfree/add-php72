<?php






class ADD_Manage_ImportEncounters implements ADD_ManageInterface {

	function getOptsList() {
		return "p:e:l:n:t::m:";
	}

	function getDescription() {
		return "Import patients into ADD";
	}

	function manage($options) {

		$page = ADD::sys()->initModules();

		$patFile = isset($options['p']) ? $options['p'] : null;
		$encFile = isset($options['e']) ? $options['e'] : null;
		$db = $add->db('patient');
		$edb = $add->db('encounter');

		if ($patFile) {
			$startTime = microtime(true);
			$patId = "patient_id";
			$patDf = new LRB_DataFile_Delimited($patFile);
			$patDf->setHeaderRow(true);
			if (isset($options['l']))
				$patDf->setLimit($options['l']);
			$patDf->open();

			$counter = 0;
			$log->info("Reading patient input file:" . $patFile);
			$pcreated = 0;
			$pupdated = 0;
			$add->beginTxn();
			$add->disableKeys("patient");
			$add->disableKeys("address");
			$add->disableKeys("gp");
			$add->disableKeys("audit");
			while ($pitem = $patDf->nextRow()) {
				$ident = $pitem[$patId];
				$pat = $db->getPatientWithIdentifier($ident);
				if (!$pat) {
					$log->info("NEW patient '$ident' (" . $pitem['first_name'] . " " . $pitem['last_name'] . " - " . $pitem['date_of_birth'] . ")");
					$db->createPatientDemographics($pitem);
					$pcreated++;
				} else {
					$currHash = md5($pat->getIdentifier() . $pat->getFirstName() . $pat->getLastName() . $pat->getDateOfBirth());
					$newHash = md5($pitem['identifier'] . $pitem['first_name'] . $pitem['last_name'] . $pitem['date_of_birth']);
					if ($currHash != $newHash) {
						$log->info("UPDATE existing patient '$ident' (" . $pat->getFirstName() . " " . $pat->getLastName() . " - " . $pat->getDateOfBirth() . ") from " . $pat->toJSON() . " to " . json_encode($pitem));
						$db->updatePatientDemographics($pat->getId(), $pitem);
						$pupdated++;
					} else {
						$log->info("EXISTING patient '$ident' (" . $pat->getFirstName() . " " . $pat->getLastName() . " - " . $pat->getDateOfBirth() . ")");
					}
				}

				$counter++;
			}
			$add->commitTxn();
			$add->enableKeys("patient");
			$add->enableKeys("address");
			$add->enableKeys("gp");
			$add->enableKeys("audit");

			$log->info("Patients created $pcreated, updated $pupdated");
			$endTime = microtime(true);
			$lot = $endTime - $startTime;
			$log->info("Process took " . round($lot, 2) . " secs. " . round($patDf->getRowCount() / $lot, 1) . " rows/second");
			$patDf->close();
		}
		if ($encFile) {

			//$log -> info("memory usage:" . memory_get_usage(true));
			$mod = $add->getModule($options['m']);
			foreach ($mod->getPages() as $page) {
				$page->setupComponents();
				$pageList[$page->getName()] = $page;
			}


			$log->info("Reading encounter input file:" . $encFile);

			$encDf = new LRB_DataFile_Delimited($encFile);
			$encDf->setHeaderRow(true);
			if (isset($options['t']))
				$encDf->setFieldSep("\t");
			$encDf->open();
			if (isset($options['l']))
				$encDf->setLimit($options['l']);
			$log->info("Process " . $encDf->lineCount() . " lines");
			$encDf->setBatchSize(500);

			global $possObsFields;
			$possObsFields = array();

			global $pholders;
			$pholders = array();

			//loop through each encounter
			$counter = 0;
			global $obsHeadings;
			$obsHeadings = array();
			$headings = array();
			foreach ($encDf->getHeadings() as $heading) {

				$type = "value";
				$text = $heading;
				$temp = explode(":", $heading);
				if (count($temp) == 2) {
					$text = $temp[0];
					$type = $temp[1];
				}

				$obsHeadings[$text] = array("text" => $text, "type" => $type);
				$headings[] = $text;
			}
			$encDf->setHeadings($headings);
			global $createCount, $updateCount, $pageList;
			$createCount = 0;
			$updateCount = 0;
			//Propel::disableInstancePooling();

			$startTime = microtime(true);
			$noRows = $encDf->lineCount();
			$er = new ADD_Import_EncounterRow($obsHeadings, $pageList);
			$log->info("Disabling table keys");
			$add->disableKeys("encounter");
			$add->disableKeys("observation");
			$add->disableKeys("audit");
			$add->disableKeys("observation_audit");

			$log->info("Reading first batch of encounters");
			while ($ebatch = $encDf->nextBatch()) {
				$batchStartTime = microtime(true);
				$add->beginTxn();


				$beforeMem = memory_get_usage(true);

				foreach ($ebatch as $eitem) {
					//$log -> info("memory usage:" . memory_get_usage(true));

					try {

						readRow($eitem, $er);
						unset($eitem);
						$rc = $encDf->getRowCount();
						if ($rc % 100 == 0)
							$log->info("Read $rc/$noRows rows");
					} catch (Exception $ex) {
						$log->error(ADD::sys()->errorMessage($ex));
						continue;
					}
				}


				//if (isset($options['n'])) {
				//	$add->rollbackTxn();
				//}
				//else {
				$add->commitTxn();

				$afterMem = memory_get_usage(true);
				$log->info("Batch complete (" . $encDf->getRowCount() . " rows processed) created $createCount encounters, updated $updateCount encounters");
				$log->info("Memory used by batch:" . ($afterMem - $beforeMem) . " ($beforeMem - $afterMem)");

				$batchEndTime = microtime(true);

				$blot = $batchEndTime - $batchStartTime;
				$log->info("Batch took " . round($blot, 2) . " secs. " . round(count($ebatch) / $blot, 1) . " rows/second");
			}
			$encDf->close();

			$log->info("Re-enabling table keys");
			$add->enableKeys("encounter");
			$add->enableKeys("observation");
			$add->enableKeys("audit");
			$add->enableKeys("observation_audit");


			$log->info("Imported file " . $encFile);
			$endTime = microtime(true);

			$lot = $endTime - $startTime;
			$log->info("Process took " . round($lot, 2) . " secs. " . round($encDf->getRowCount() / $lot, 1) . " rows/second");
			unset($encDf);
			$log->info("IMPORT COMPLETE");
		}

		function readRow(&$eitem, $er) {
			global $createCount, $updateCount, $log, $pholders;

			//$log -> info("memory usage before getPatient:" . memory_get_usage(true));
			if (!$er->populate($eitem))
				return;

			if (!$er->getPatient())
				return;

			//$log -> info("memory usage before correctHash:" . memory_get_usage(true));

			if (!$er->correctHash())
				return;

			//loop through possible observation field names and populate the observation hash from the file row
			//$log -> info("memory usage before updateEncounter:" . memory_get_usage(true));

			list($created, $enc) = $er->updateEncounter($pholders);

			//$log -> info("memory usage after updateEncounter:" . memory_get_usage(true));
			//if the ID field has a value then use this as the placeholder key
			if ($enc && $enc->getId()) {
				$pholders[$er->getIdentifier()] = $enc->getId();
				if ($created) {
					$createCount++;
				} else {
					$updateCount++;
				}
			}
			unset($enc);
			unset($er);
		}
	}
}