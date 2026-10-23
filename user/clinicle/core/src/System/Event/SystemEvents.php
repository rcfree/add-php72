<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Event;

final class SystemEvents {
    const AFTER_INIT = "systemEvents.afterInit";
    const BEFORE_INIT = "systemEvents.beforeInit";
    const COMMAND_REGISTER="systemEvents.commandRegister";
    const AFTER_RESOLVE = "systemEvents.afterResolve";
}