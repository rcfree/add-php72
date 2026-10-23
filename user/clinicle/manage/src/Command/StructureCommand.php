<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

class StructureCommand extends \ClinicLE\Command\AbstractCommand
{
    private $ppcount = 1;
    private $cpcount = 1;
    private $ignoreModules = array("cap", "research", "patient", "qol");
    private $prev = null;

    function getOptsList()
    {
        return "";
    }

    function getDescription()
    {
        return "Structure as CSV";
    }

    function runCommand(\ClinicLE\Common\Config\AbstractConfig $options)
    {
        $add = ADD::sys();
        $log = ADD::log($this);

        $add->initModules();
        $mods = $add->getModules(true);

        $mcount=1;
        foreach ($mods as $mod) {
            $lcount=0;
            if (in_array($mod->getName(), $this->ignoreModules)) continue;

            $tpages = $mod->getTopPages();

            foreach ($tpages as $tpage) {
                if (!$tpage->getSetting('encounter')) continue;

                $opath = array(sprintf("%03d",$mcount) . "#" . $tpage->getTitle());
                print implode(".", $opath) . "," . $tpage->getSetting('encounter') . "\n";
                $pcount = 1;
                $lcount++;
                foreach ($tpage->getChildPages() as $page) {
                    if (!$page->getSetting('encounter')) continue;
                    if (!$page->getSetting('components')) continue;

                    $path = array_merge($opath, array(sprintf("%03d",$pcount) . "#" . $page->getTitle()));
                    print implode(".", $path) . "," . $page->getSetting('encounter') . "\n";
                    $lcount++;
                    $ccount = 1;

                    foreach ($page->getChildPages() as $cpage) {
                        if (!$cpage->getSetting('encounter')) continue;
                        if (!$cpage->getSetting('components')) continue;

                        $cpath = array_merge($path, array(sprintf("%03d",$ccount) . "#" . $cpage->getTitle()));
                        print implode(".", $cpath) . "," . $cpage->getSetting('encounter') . ",".$cpage->getSetting('date_field')."\n";
                        $ccount++;
                        $lcount++;
                    }

                    $pcount++;
                }
                //$opath = $this->getOutputPath($page,$count);

                //$modPageCount++;
                //$this->cpcount++;
                //$count++;

            }
            if ($mod->getSetting('extends')) continue;
            if ($lcount==0) continue;
            $mcount++;
        }
    }

    function getOutputPath($page)
    {
        $opath = array($this->getCount($page) . "#" . $page->getSetting('title'));
        //$currPage = $page;

        $ppage = $page->getParentPage();
        if ($ppage) {
            array_unshift($opath, $this->getCount($ppage) . "#" . $ppage->getSetting('title'));
            $gppage = $ppage->getParentPage();
            if ($gppage) {
                array_unshift($opath, $this->getCount($gppage) . "#" . $gppage->getSetting('title'));
            }
        } else {

        }
        return $opath;
    }

    private function getCount($page)
    {
        $ppage = $page->getParent();
        $ppname = $ppage ? $ppage->getName() : "BASE";
        if (!isset($this->counts[$ppname])) {
            $this->counts[$ppname] = 0;
        }
        if ($this->prev != $ppname) $this->counts[$ppname]++;
        $counts = $this->counts[$ppname];
        $this->prev = $ppname;

        return $counts;
    }

}
