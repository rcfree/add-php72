<?php

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 18/10/2016
 * Time: 16:37
 */
class ADD_Component_SingleTestSubmit extends ADD_Component
{
    function beforeSubmit(&$values)
    {
        $web = ADD::web();
        $ddb = ADD::db('dictionary');

        $testVal = ADD_Util::val('allergen_test', $values);
        $testTitle = ADD_Util::val('__allergen_test_ac', $values);
        $dictName = $this->assertSetting('dictionary');
        $dict = $ddb->getDictionaryByName($dictName);
        $di = $ddb->getDictionaryItemWithValue($dict, $testVal);

        if (!$di || $testTitle != $di['title']) {
            return $this->createNewTest($testTitle, $dictName, $values);
        }
    }

    private function createNewTest($testTitle,$dictName, &$values)
    {
        $web = ADD::web();
        $ddb = ADD::db('dictionary');
        $page = $this->getPage();

        $testTitle = ucfirst($testTitle);
        $web->addWarning("Created new " . $page->getSetting('test_single') . " '$testTitle'");
        $di = $ddb->storeDictionaryItem($dictName, array('title' => $testTitle));
        $values['allergen_test'] = $di['value'];
        return;
    }
}