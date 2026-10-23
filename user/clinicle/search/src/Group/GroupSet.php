<?php
namespace ClinicLE\Search\Group;
use ClinicLE\Common\Util;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:50
 */
class GroupSet
{
    private $groups = array();
    private $categories = array();
    private $groupCategories = array();
    function addGroup(AbstractGroup $group) {
        if(Util::isEmpty($group->getName())) {
            throw new \InvalidArgumentException("Group name cannot be empty");
        }
        $this->groups[$group->getName()]=$group;
        $this->addGroupToCategory($group);
    }
    function setCategory($name, $description) {
        $this->categories[$name]=$description;
    }
    function addGroupToCategory($group) {
        $this->groupCategories[$group->getCategoryName()][]=$group->getName();
    }
    function getGroups() {
        return $this->groups;
    }



    /**
     * @param $name
     * @return AbstractGroup
     */
    function getGroup($name) {
        return Util::val($name,$this->groups);
    }
    function getCategories() {
        return $this->categories;
    }

    function getGroupsInCategory($categoryName) {
        $groupCategories = Util::val($categoryName,$this->groupCategories,array());
        $options = array();
        foreach($groupCategories as $groupCategory) {
            $options[] = array("value" => $groupCategory, "label" => $this->getGroup($groupCategory)->getTitle());
        }
        return $options;
    }
    function getGroupDescription($groupName) {
        $group = $this->getGroup($groupName);
        $categoryName = $group->getCategoryName();
        $categoryTitle = $this->categories[$categoryName];
        return $categoryTitle.": ".$group->getTitle();
    }
    /**
     * @return array
     */
    public function getGroupCategories()
    {
        return $this->groupCategories;
    }
}