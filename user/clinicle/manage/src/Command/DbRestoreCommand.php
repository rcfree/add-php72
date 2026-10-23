<?php
/**
 * Description of Load
 *
 * @author rcf8
 */

namespace ClinicLE\Manage\Command;

use ADD\ResphyImport\Exception\SystemCommandException;
use ClinicLE\Common\Util;
use DateTime;
use Exception;
use CLE;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbRestoreCommand extends Command
{

    protected function configure() {
        $this
            ->setName("db-restore")
            ->setDescription("Database restore tool")
            ->setDefinition(array(
                new InputOption("date","d",InputOption::VALUE_OPTIONAL,"Date/time of backup to use. This can either be a date in ISO format or a string recognised by PHP (e.g. yesterday 18:00)."),
                new InputOption("quiet","q",InputOption::VALUE_NONE,"Quiet mode automatically accepts drop database etc."),
                new InputOption("login","l",InputOption::VALUE_OPTIONAL,"DB login credential path. If this is not used, db-restore expects deprecated user/password options"),
                new InputOption("pass","p",InputOption::VALUE_OPTIONAL,"(deprecated) DB password for user. If this is not used, you will be prompted for a password"),
                new InputOption("user","u",InputOption::VALUE_REQUIRED,"(deprecated) DB user with ability to drop and recreate database"),
             ))->setHelp(<<<EOT
The <info>db-restore</info> command will restore the main database from a gzipped SQL dump. The source location 
dump defaults to /media/uhlbackup/live_db and the database name is add_data_live.sql.gz. However these can be 
changed by setting config options in the 'DbRestore' section ('backup_path' and 'backup_db'). 

You can easily specify a different date/time using the -d option. This is also affected by the 'backup_pattern' setting 
which indicates the MM part of HH::MM to use. 

The layout of the backup directories inside the backup path should be Y/m/d/Hi (e.g. 2017/08/09/1200)

Usage:
<info>clinicle db-restore -d"yesterday 18:00" --login=user_backup -q</info>

EOT
            );
    }
    public function execute(InputInterface $input, OutputInterface $output) {
        $add = CLE::sys();
        $log = CLE::log($this);

        $db = CLE::db();
        $login = $input->getOption("login");
        $user = $input->getOption("user");
        if(!$login && !$user) {
            throw new InvalidOptionException("login/l OR user/u option must be provided");
        }
        $restConf = $add->getConfig()->getSection("DbRestore");
        $create = false;
        if(!$restConf) {
            $restConf = new \Clinicle\Common\Config\ArrayConfig(array());
        }
        try {
            if(is_a($db,"ClinicLE\Common\Connection\AbstractConnection")) {
                @$db->getPDO();
            }
            else{
                @$db->getConn();
            }
        }
        catch(\PDOException $ex) {
            if(preg_match("/Unknown database/",$ex->getMessage())) {
                $log->warn("Creating new database");
                $create=true;
            }
            else {
                $log->error("Error:".$ex->getMessage());
                return;
            }
        }

        $config = $add->getConfig()->getSection('Database');
        $dt = new DateTime($input->getOption("date"));
        $dbPath = $dt->format("Y/m/d/H").$restConf->getSetting
            ('backup_pattern',"00");

        $quiet = $input->getOption("quiet");
        if(!$quiet) {
            $output->writeln("This WILL drop the existing ADD database (".$config->getSetting('dsn').") and restore from '$dbPath'\nAre you sure (Y/n)?");
            $value = Util::inputLine(1);
            if ($value!="Y") {
                throw new SystemCommandException("User cancelled operation");
            }
        }

        if($login ) {
            $credentials = "--login-path=$login";
        }
        else {
            $pass = $input->getOption("pass");
            if(!$pass) {
                echo "Enter the DB password for this user (".$user."):\n";
                $pass = self::getPassword(true);
            }

            if (!$pass) {
                throw new SystemCommandException("No login or password - user cancelled operation");
            }
            $credentials = "-u$user -p$pass";
        }
        $output->writeln("");

        if (!$create) {
            $this->system("mysqladmin ". $credentials." -h".$db->getDbHost().($quiet ? " --force " :
                    "")." drop ".$db->getDbName());
        }

        $this->system("mysqladmin $credentials -h".$db->getDbHost()." create ".$db->getDbName());
        $backupPath = $restConf->getSetting('backup_path',"/media/uhlbackup/live_db/");
        $backupDbName = $restConf->getSetting('backup_db',"add_data_live");

        $fullDbPath = $backupPath.$dbPath;

        chdir($fullDbPath);
        $log->info("Restoring file: ".$fullDbPath."/$backupDbName.sql.gz");
        if(!file_exists($backupDbName.".sql.gz")) {
            throw new SystemCommandException("Database backup file '$backupPath/$backupDbName.sql.gz' does not exist");
        }
        $sys = "zcat $backupDbName.sql.gz | mysql $credentials -h".$db->getDbHost()." "
            .$db->getDbName();
        $this->system($sys);
    }
    static function getPassword($stars = false)
    {
        // Get current style
        $oldStyle = shell_exec('stty -g');

        if ($stars === false) {
            shell_exec('stty -echo');
            $password = rtrim(fgets(STDIN), "\n");
        } else {
            shell_exec('stty -icanon -echo min 1 time 0');

            $password = '';
            while (true) {
                $char = fgetc(STDIN);

                if ($char === "\n") {
                    break;
                } else if (ord($char) === 127) {
                    if (strlen($password) > 0) {
                        fwrite(STDOUT, "\x08 \x08");
                        $password = substr($password, 0, -1);
                    }
                } else {
                    fwrite(STDOUT, "*");
                    $password .= $char;
                }
            }
        }

        // Reset old style
        shell_exec('stty ' . $oldStyle);

        // Return the password
        return $password;
    }
    function system($command, $return_var=null) {
        system($command, $return_var);
    }

}
?>