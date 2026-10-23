<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 5/6/15
 * Time: 8:40 PM
 */

namespace ClinicLE\System\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
class EventDispatcher extends BaseEventDispatcher {
	public function dispatch($eventName, \Symfony\Component\EventDispatcher\Event $event = null) {
	    parent::dispatch($eventName, $event);
	}
	public function dispatchToPage($eventName, $page, Event $event=null) {
	    $module = $page->getModule();
	    $this->dispatch(implode(".",array($module->getName(),$page->getName(),$pageEventName)),$event);
	}
    public function dispatchMultiple(array $eventNames, Event $event=null) {
        foreach($eventNames as $eventName) {
            $this->dispatch($eventName,$event);
            if($event->isPropagationStopped()) {
                return true;
            }
        }
        return false;
    }
        
}
