<?php
/**
 * Description of Load
 *
 * @author rcf8
 */
namespace ClinicLE\Manage\Command;

use ClinicLE\Command\AbstractCommand;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Config\AbstractConfig;
use ADD;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wrep\Daemonizable\Command\EndlessCommand;
use CLE;
class CleanupCommand extends EndlessCommand
{

    protected function configure()
    {
        $this->setName('cleanup')
            ->setDescription('Login, temporary files and recent patient cleanup')
            ->setTimeout(3700);
    }

    function getDescription() {
        return "Cleanup submit tokens, logins and recent patient lists";
    }
    function initialize(InputInterface $input, OutputInterface $output)
    {
        CLE::log($this)->running();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbm = CLE::svc(CoreServices::DATABASE_MANAGER);
        $dbm->_db=array();
        
        $this->throwExceptionOnShutdown();

        $log = CLE::log($this);

        $ffinder = CLE::sys()->getService(CoreServices::FILEFINDER);
        $ffinder->in(CLE::sys()->getPath()."/tmp");
        $ffinder->date("before yesterday");
        $ffinder->files();

        $fileCount = 0;
        foreach($ffinder as $file) {
            unlink($file->getPathname());
            $fileCount++;
        }
        $log->info("Cleaned up ".$fileCount." temp file(s)");

        $db = CLE::sys()->getService(CoreServices::MAIN_CONNECTION);
        $timestamp = time() - (24*60*60);

        $db->execute("delete from submit_token where timestamp < ?",array($timestamp));

        $log->info("Deleted submit tokens from before today");

        $db->execute("delete from recent_patient where patient_id is null");

        $recentPatientUsers = $db->fetchAll("select * from recent_patient group by username");
        $log->info("Clean up recent patients");

        foreach($recentPatientUsers as $recentPatientUser) {
            $recentPatients = $db->fetchAll("select * from recent_patient where username=? group by patient_id order by timestamp desc limit 0,30",$recentPatientUser['username']);
            $recentCount = count($recentPatients);
            if($recentCount==0) continue;
            $db->execute("delete from recent_patient where timestamp<? and username=?",array($recentPatients[$recentCount-1]['timestamp'],$recentPatientUser['username']));
        }

        $log = CLE::log($this);
        $dbm->_db=array();
        \ADD_Database_User::$_conn = null;
        
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
