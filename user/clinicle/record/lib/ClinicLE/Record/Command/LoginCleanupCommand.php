<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/01/17
 * Time: 16:26
 */

namespace ClinicLE\Record\Command;


use ClinicLE\Command\AbstractCommand;
use ClinicLE\Common\Config\AbstractConfig;
use CLE;

class LoginCleanupCommand extends AbstractCommand
{
    function getOptsList()
    {
        return "";
    }

    function getDescription()
    {
        return "Login cleanup";
    }

    function runCommand(AbstractConfig $options)
    {
        $log = CLE::log($this);
        $udb = CLE::db('user');
        $rdb = CLE::db('record');
        $expiredRecords = $rdb->getLoggedInUserRecordsOlderThan('02:00:00');

        foreach($expiredRecords as $expiredRecord) {
            $addSessionId = $expiredRecord['session_id'];

            $log->info("Logout user '".$expiredRecord['username']."' which expired ".$expiredRecord['timestamp']. " with session ID '$addSessionId'");
            $user = new \stdClass;
            $user->username=$expiredRecord['username'];
            $ul = new \ADD_UserLogin($user,$addSessionId);
            $ul->logout();

            $log->info("Remove session:".$addSessionId);
            $udb->deleteSessionsWithSessionId($addSessionId);
        }
    }
}