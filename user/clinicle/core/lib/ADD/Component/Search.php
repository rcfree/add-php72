<?php
use ClinicLE\Common\Util;

class ADD_Component_Search extends ADD_Component {
	const DECEASED_MARKUP="<span style='display:inline;margin-left:1em' class='label label-important'><i class='icon-white icon-exclamation-sign'></i>&nbsp;Deceased</span>";
	private function boldTerms($string, $terms) {
		return str_ireplace($terms,"<b>$terms</b>",$string);
	}
	function title(&$title) {
		$title="Search patients";
	}
	function displayAccess() {
        $page = $this->_page;
		if (!$page->getPermission('search')) {
			throw new ADD_NoPermissionToAccessException("Sorry you do not have permission to search patients.");
		}
	}
	private $hadInput = false;
	private $terms;
	private $fname;
	private $lname;
	private $dob;
	private $searchSel;
	private $args;
	
	function submit($values) {
		$add = ADD::sys();
		$this->hadInput = false;
		
		$this->terms = Util::val('terms',$values);
		if ($this->terms) $this->hadInput = true;
		
		$this->fname = Util::val('first_name',$values);
		if ($this->fname) $this->hadInput = true;
		
		$this->lname = Util::val('last_name',$values);
		if ($this->lname) $this->hadInput = true;
		
		$this->dob = Util::val('dob',$values);
		if ($this->dob) $this->hadInput = true;
		
		$this->searchSel = Util::val('search_sel',$values,1);
		
		// Form elements
		$this->args = $this->searchSel==1 ? array('id'=>$this->terms,'fname'=>null,'lname'=>null,'dob'=>null,'raw'=>true,'search_sel'=>$this->searchSel) : array('id'=>null,'fname'=>$this->fname, 'lname'=>$this->lname, 'dob'=>$this->dob,'raw'=>true,'search_sel'=>$this->searchSel);
		$this->values = $values;
		CLE::info("values:",$values);
	}
	function generate() {
		$add = ADD::sys();
        $page = $this->_page;
		if (!is_array($this->args)) {
			$this->args = array(
				'id' => null,
				'fname' => null,
				'lname' => null,
				'dob' => null,
				'raw' => true,
				'search_sel' => 1,
			);
		}
		CLE::info("generate search component",$this->args);
		
        $page->addJavascript("field('dob').mask('99/99/9999')");
		
		$html = $page->html();
		
		$this->addSearchElements($this->args,$page);
		 
		$form = array();
		
		$header = array('Patient Name','Date of Birth','NHS number','S number','Address1','Status');
		$pdb = ADD::db('patient');
                $sdb = ADD::db('search_patient');
		                               $pmi=null;
              try {
                       $pmi = ADD_Util::createPMI($this);
            }
              catch(Exception $exception) {
              	                               ADD::web()->addError("The UHL patient lookup service is not working. Please add patients manually. They will be updated automatically in the future.");
             }
             $records = null;

		$results = array();
		$noPmiResults = false;
		$resultCount = 0;
		if ($this->hadInput) {
			$records = array();
			try {
				 $records = $pmi ? $pmi->get_patient_records($this->args) : array();
			}
			catch(Exception $ex) {
				$msg = $ex->getMessage();
				if (!preg_match("/patient with identifier/",$msg)) {
					if (!preg_match("/is not available/",$msg)) {
						$warning = "";
						if ($this->searchSel == 1) {
							$warning="To search please enter an S number/hospital number/keyword";
							if (strlen($this->terms)==0) $this->hadInput=false;
						}
						if ($this->searchSel == 2) {
							$warning = "To search patients in the hospital system as well as ADD please enter ";
							$warning.="a first name, last name and date of birth";
						}
						//$warning.=$ex->getMessage();
						$page->addWarning($warning);
					}
					$noPmiResults=true;
				}
			}
			$pg = $add->getParam('page');
			if(!$pg) $pg=1;
			$rows=$add->getParam('rows');
			if(!$rows) $rows=20;
			$patMod = $add->getModule('patient');
			$addPerm = $patMod->getPage('new')->getPermission('create');
		
			if (count($records)>0) {
				foreach($records as $record) {
				 	$p = $pdb->getPatientWithIdentifier($record['main_pat_id']);
					$result = array();
					if ($p) {
						$temp = $this->getResultsFromPatients(array($p));
						$result=$temp[0];
					}
					else {
						$options = array(
							array("tag"=>"span","type"=>ADD_Content_Html::TEXT,"text"=>"*","attrs"=>array("style"=>"color:red;font-size:120%")),
							array("type"=>ADD_Content_Html::MARKUP,"text"=>$record['first_name']." ".$record['last_name']."&nbsp;"),
						);

						if ($addPerm) {
							$options[]=array(
								"tag"=>"a",
								"type"=>ADD_Content_Html::TEXT,
								"text"=>"<i class='icon-plus-sign icon-white'></i>&nbsp;Add",
								"attrs"=>array("href"=>"patient/new?identifier=".$record['main_pat_id'],"class"=>"btn btn-primary btn-small")
							);	
							$options[]="&nbsp;";
							
							$options[]=array(
								"tag"=>"a",
								"type"=>ADD_Content_Html::TEXT,
								"text"=>"<i class='icon-plus-sign'></i>&nbsp;Quick add",
								"attrs"=>array("href"=>"patient/quickadd?identifier=".$record['main_pat_id'],"class"=>"btn btn-small")
							);
							
						}

						$result[] = $options;
						$result[] = $record['dob'];
						$result[] = $record['nhs_number'];
						$result[] = $record['main_pat_id'];
						$result[] = $record['pat_addr1'];
						$result[] = ADD_Util::val('death_indicator',$record)==1 ? self::DECEASED_MARKUP : "";
					}
	
					$results[] = $result;
				}
				$resultCount = count($results);
				$firstIndex = ($pg * $rows) - $rows;
				$lastIndex = count($results) + $firstIndex;
	        }
	        else {
	        	$noPmiResults=true;
	        	$output = $this->searchSel==1 ? $sdb->findPatientsMatchingTerms($this->terms, $pg, $rows) : $sdb->findPatientsMatchingNameAndDob($this->values, $pg, $rows);
					$patients = $output['data'];
					$resultCount = $output['count'];
					$firstIndex = $output['first'];
					$lastIndex = $output['last'];
				$results = $this->getResultsFromPatients($patients);
				
	        }
		}
		$msg = "'{$this->terms}'";
		if ($this->searchSel != 1) {
			$temp = array();
			if ($this->fname) $temp[]="first name = '$this->fname'";
			if ($this->lname) $temp[]="last name = '$this->lname'";
			if ($this->dob) $temp[]="date of birth = '$this->dob'";
			$msg=implode(" or ",$temp);
		}
			
		if ($resultCount>0) {
			$patText = "<b>".$resultCount." ".ADD_Util::pluralize($resultCount,"matching patient")."</b> in ADD";
			$resultInfo = "$patText".($noPmiResults ? "" : " or the hospital's patient database")."<br/>";
			$resultInfo .= "To select a patient click their name in the table below.";
			
			$html->addTextElement("div",$resultInfo,array("class"=>"alert alert-success"));
			
			$html->startElement("div");
			if ($resultCount>1) $html->addTextElement("span","Showing ".$firstIndex." - ".$lastIndex,array("style"=>"padding-right:0.5em"));
			if ($resultCount>$lastIndex) {
				$params = $add->getParams();
				$params['page']=$pg+1;
				$html->addTextElement("a","Show more matches..",array("href"=>ADD_Util::uri("search",$params),"style"=>"display:inline","class"=>"btn-small btn"));
			}
			
			$html->startElement("table",array("class"=>"table table-hover"));
			$html->startElement("thead");
			$html->startElement("tr");
			foreach($header as $heading) {
				$html->addTextElement("th",$heading);
			}
			$html->endElement("tr");
			$html->endElement("thead");
			$html->startElement("tbody");
			
			foreach($results as $result) {
				$html->startElement("tr");
				foreach($result as $cell) {
					$html->startElement("td");
					is_array($cell) ? $html->addHtmlArray($cell) : $html->addHtml($cell,null,true);
					$html->endElement("td");
				}
				$html->endElement("tr");
			}
			
			$html->endElement("tbody");
			$html->endElement("table");
			if (!$noPmiResults) {
				if ($addPerm) {
					$html->addTextElement("p","<b>Starred (*) patients do not currently exist in ADD and will be created automatically if you click their 'Add' button.</b>",array("class"=>"text text-error"));
				}
				else {
					$html->addTextElement("p","<b>Starred (*) patients do not currently exist in ADD. You do not have permission to create new patients.</b>",array("class"=>"text text-error"));
				}
			}
		}
		
		else {
			if ($this->hadInput) {
				$html->addTextElement("div","Sorry <b>no matching patients</b> were found in ADD or the hospital database.",array("class"=>"alert alert-error"));
			}
		}
		
		 ADD::db('audit')->auditSearch($add->getParams());
	}

	public function menuPath(&$menuPath) {
		$menuPath = "search";
	}
	
	protected function addSearchElements($args) {
        $page = $this->_page;
		$html = $page->html();
        $args = $this->args; 
		$html->startElement("form",array("action"=>"/search", "onsubmit"=>"return false;", "ng-submit"=>"save(\$event)", "method"=>"post"));
		$html->startElement("div");
		$html->addTextElement("label","How do you want to search for patients?");
		foreach(array(1=>"by S number, case note/hospital number", 2=>"by name and date of birth") as $value => $label) {
			$attrs = array("class"=>"radio", "type"=>"radio","value"=>$value,"name"=>"search_sel",'onclick'=>"toggleSearch('search_sel')","autocomplete"=>"false");	
			if ($args['search_sel']==$value) $attrs['checked']='checked';	 
			$html->addElement("input",$attrs);
			$html->addTextElement("span","&nbsp;".$label,array("style"=>"padding-right:15px"));
		}
		
		$html->endElement("div");
		
		$html->startElement("div");
		$html->startElement("span",array("id"=>"search-terms","style"=>($args['search_sel']==2 ? "display:none" : "display:block").";padding-top:10px"));
		$html->addTextElement("label","Enter an S number or hospital number");
		$html->addElement("input",array("type"=>"text","size"=>15,"maxlength"=>15,"name"=>"terms","value"=>$args['id']));
		$html->endElement("span");
		$html->endElement("div");
		
		$html->startElement("div",array("style"=>"padding-top:10px"));
		$html->startElement("span",array("id"=>"search-name-dob","style"=>($args['search_sel']==1 ? "display:none" : "display:block")));
		$html->addTextElement("p","To include the hospital database in your search you must enter a full name and DOB",array("class"=>"text-info"));
		$html->startElement("div",array("style"=>"float:left;padding-right:1em"));
		$html->addTextElement("label","Enter first name");
		$html->addElement("input",array("type"=>"text","size"=>30,"maxlength"=>40,"name"=>"first_name","value"=>$args['fname']));
		$html->endElement("div");
		$html->startElement("div",array("style"=>"float:left;padding-right:1em"));
		$html->addTextElement("label","Enter last name");
		$html->addElement("input",array("type"=>"text","size"=>30,"maxlength"=>40,"name"=>"last_name","value"=>$args['lname']));
		$html->endElement("div");
		$html->addTextElement("label","Enter date of birth");
		$html->startElement("div",array("style"=>"float:left;padding-right:1em"));
		$html->addElement("input",array("type"=>"text","size"=>10,"maxlength"=>10,"name"=>"dob","style"=>"width:6em","value"=>$args['dob']));
		$html->endElement("span");
		$html->endElement("div");
		$html->endElement("div");
  
        $html->addTextElement("button","<i class='icon-search icon-white'></i>&nbsp;Search", array("class"=>"btn btn-primary","style"=>"display:block;clear:left"));
       
		$html->endElement("form");
	}

	private function getResultsFromPatients($patients) {
		$add = ADD::sys();
		$patMod = $add->getModule('patient');
		$patDel = $patMod->getPage('delete')->getPermission('delete');
		$patPurge = $patMod->getPage('purge') && $patMod->getPage('purge')->getPermission('purge');
		$results = array();
		foreach($patients as $p) {
			$result = array();	
			$names = $p['first_name']." ".$p['last_name'];
			if ($p['is_deleted']) {
				$result[]= $names.=" <span class='label label-important'>Deleted</span>";
			}
			else {
				$uri = ADD_Util::uri("",array("pid"=>$p['id'],"t"=>ADD::db('token')->generate()));
				$result[] = array( array("tag"=>"a","text"=>$names, "attrs"=>array("onclick"=>"ADD.Notice.startWait()","href"=>$uri),"type"=>ADD_Content_Html::TEXT));
			}
			$result[] = $p['date_of_birth'];
			$result[] = $p['nhs_no'];
			$result[] = $p['identifier'];
			$result[] = isset($p['address_line_1']) ? $p['address_line_1'] : "- none -";
			$result[] = $p['deceased']==1 ? self::DECEASED_MARKUP : "";
			
			$options = array();
			if (!$p['is_deleted']) {
				if ($patDel) {
					$options[] = array("tag"=>"a","text"=>"<i class='icon-remove'></i>&nbsp;Delete", "attrs"=>array("href"=>"javascript:;","onclick"=>"deletePatient({$p['id']});","cle-nong"=>"","class"=>"btn btn-small"),"type"=>ADD_Content_Html::TEXT);
				}
			}
			if ($patPurge) {
				$options[] = array("tag"=>"a","text"=>"<i class='icon-fire'></i>&nbsp;Purge", "attrs"=>array("href"=>"javascript:;","onclick"=>"purgePatient({$p['id']});","cle-nong"=>"","class"=>"btn btn-small"),"type"=>ADD_Content_Html::TEXT);
			}
			$result[]=$options;
			$results[]=$result;
		}
		return $results;
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('search');
	}
	
	function afterGenerate() {
        $page = $this->_page;
		$html = $page->html();
		$html->startElement("div",array("id"=>"modal-remove","class"=>"modal hide fade","style"=>"width:500px"));
		$html->startElement("div", array("class"=>"modal-body"));
		$html->addTextElement("div","",array("id"=>"modal-remove-text"));
		
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-footer"));
		$html->addTextElement("a","No, thanks",array("href"=>"javascript:;","onclick" => "$('#modal-remove').modal('hide')","class"=>"btn","id"=>"modal-no"));
		$html->addTextElement("a","Yes, please",array("href"=>"javascript:;","class"=>"btn btn-primary","id"=>"modal-confirm-yes"));
		$html->endElement("div");
		$html->endElement("div");
	}	
    
    function baseContent() {
        $page = $this->_page;
		$search = ADD::web()->getContent('search');	
		if (!$page->getPermission('search')) return;
		$search->startElement("form",array("class"=>"navbar-form pull-right", "action" => $page->createMenuPath(),"method"=>"post"));
		$search->startElement("div",array("class"=>"input-append"));
		$search->addElement("input",array("class"=>"span2","name"=>"terms","type"=>"text","placeholder"=>"Enter patient no"));
		$search->startElement("button",array("type"=>"submit","class"=>"btn"));
		$search->addTextElement("i","",array("class"=>"icon-search"));
		$search->addHtml("&nbsp;Search");
		$search->endElement("button");
		$search->endElement("div");
		$search->endElement("form","search_patient_end");
	}
}
