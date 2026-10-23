<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 30/01/17
 * Time: 10:30
 */

namespace ClinicLE\Search\Resource;


use ClinicLE\Common\Util;
use ClinicLE\Security\AccessManager;
use ClinicLE\Search\Group\GroupSet;

class SearchSessionData
{
    private $session; /** @var /CI_Session */
    private $groupSet;
    private $sessionPath;

    function __construct(AccessManager $accessManager, GroupSet $groupSet) {
        $this->session = $accessManager->getSession();
        $this->groupSet = $groupSet;
        $this->sessionPath = \CLE::sys()->getPath()."/search/sessions";
    }
    function initSearchData($force=false) {
        if(!$force && $this->getSearchData()) {
            return;
        }
        $this->storeSearchData(array("groups"=>array(),"title"=>""));
    }

    function hasSearchData() {
        return $this->getSearchData()!==null;
    }
    function getSearchId() {
        $searchId = $this->session->userdata('search_id');
        if(!$searchId) {
            $sessionId = $this->session->userdata('session_id');
            $searchId = $sessionId;
            $this->session->set_userdata('search_id',$searchId);
        }
        return $searchId;
    }
    function getSearchData() {
        $sessionId = $this->getSearchId();
        $searchFile = file_get_contents($this->sessionPath."/".$sessionId);
        return Util::unserialize($searchFile);
    }
    function storeSearchData(array $searchData) {
        $sessionId = $this->getSearchId();
        file_put_contents($this->sessionPath."/".$sessionId, Util::serialize($searchData));
    }
    function getSearchGroups() {
        $searchData = $this->getSearchData();
        return $searchData['groups'];
    }
    function getSearchGroup($id) {
        $searchGroups = $this->getSearchGroups();
        $searchGroup = Util::val($id-1,$searchGroups);
        return $searchGroup;
    }
    function getSearchGroupPart($id, $partKey) {
        $searchData = $this->getSearchGroup($id);
        $searchPart = Util::val($partKey,$searchData,array());
        return $searchPart;
    }
    function storeSearchGroupPart($id,$partKey,$partValues) {
        $searchData = $this->getSearchData();

        $searchData["groups"][$id-1][$partKey] = $partValues;
        $this->storeSearchData($searchData);
    }
    function storeSearchGroup($id,$groupData) {
        $searchData = $this->getSearchData();

        $searchData["groups"][$id-1]=$groupData;

        $this->storeSearchData($searchData);
    }
    function addSearchGroup($groupData) {
        $searchData = $this->getSearchData();

        $searchData["groups"][]=$groupData;
        $this->storeSearchData($searchData);
    }
    function removeSearchGroup($id) {
        $searchData = $this->getSearchData();
        unset($searchData["groups"][$id-1]);
        $reorderedGroups = array();
        $count = 0;
        foreach($searchData["groups"] as $searchGroup) {
            $searchGroup['id']=$count+1;
            $reorderedGroups[]=$searchGroup;
            $count++;
        }
        $searchData["groups"]=$reorderedGroups;
        $this->storeSearchData($searchData);
    }
    function initSearchGroup($groupName,$groupTitle) {
        $this->initSearchData();
        $searchGroups = $this->getSearchGroups();
        $count = count($searchGroups);
        $searchGroup=array(
            "id"=>$count + 1,
            "title"=>$groupTitle,
            "name"=>$groupName,
            "filters"=>array(),
            "attributes"=>array(),
            "settings"=>array(
                "internal_logic"=>"OR",
                "external_logic"=>"AND",
                "join_type"=>"RIGHT",
                "enabled"=>1
            ),
        );
        $this->addSearchGroup($searchGroup);
    }
    function getGroupWithId($id) {
        $searchGroup = $this->getSearchGroup($id);
        $groupName = $searchGroup['name'];
        return $this->groupSet->getGroup($groupName);
    }
}