#!/usr/bin/perl
 
use strict;
use warnings;
use Env;
use lib "/local/perl/lib/perl5/lib/perl5";
use lib $ENV{LRB_PATH}."/lib";
use Mail::Sendmail;
use Net::Domain qw(hostname);
use Config::IniFiles;
use Data::Dumper qw(Dumper);
use Log::Log4perl qw(:easy);
use IPC::Run3;
use File::Basename qw(basename);
use File::Path qw(make_path);
use rollbackup;

my @errors = ();

my $cfg = Config::IniFiles->new( -file => $ENV{LRB_PATH}."/config/backupdb.ini", -fallback => 'GENERAL' );
my @db_list = split(/\s/,$cfg->val('GENERAL','dblist'));
 
my $dbuser = $cfg->val('GENERAL','dbuser');
my $dbpass = $cfg->val('GENERAL','dbpass');
my $dbhost = $cfg->val('GENERAL','dbhost');
my $tempdir = $cfg->val('GENERAL','tempdir');
my $archivedir = $cfg->val('GENERAL','archivedir');
my $email = $cfg->val('GENERAL','fail_email');
if ( !-e $tempdir ) {
       die "Sorry the temp directory '$tempdir' does not exist";
}
if ( !-e $archivedir ) {
       die "Sorry the archive directory '$archivedir' does not exist";
}
my $remotedir = $cfg->val('GENERAL','remotedir');
Log::Log4perl->easy_init({level=>$DEBUG,file => "/local/lrbtools/log/backup.log"});

my ( $today, $time ) = get_datetime();
eval {
       my $sourcedir = create_source_dir( $today, $time );
       chdir $sourcedir;
 
       backup_databases( \@db_list, $dbuser, $dbpass, $dbhost );
 
       copy_to_remote( $sourcedir, $today, $time );
       
       roll_backups($today,$remotedir,$archivedir);
};

if ($@) {
	mail_on_fail("FATAL",$@);
    die $@;
}

if ( scalar(@errors) > 0 ) {
       foreach my $err (@errors) {
              ERROR $err;
       }
       mail_on_fail("RUNTIME",join("\n",@errors));
}

sub mail_on_fail {
	my $error_type=shift;
	my $error = shift;
	my %mail = ( 
			To      => $email,
            From    => 'rcf8@le.ac.uk',
            Subject => "[backupDb.pl] ".uc($error_type)." ERROR on '".hostname()."'",
            Message => "Fatal error during backup:\n\nhost:".hostname()."\nerror:".$error
    );

 	sendmail(%mail) or die $Mail::Sendmail::error;
}
sub sysrun {
 
       my ( $line, $no_run3 ) = @_;
 
       !$line and die "line is undef";
 
       my ( $out, $err ) = ( "", "" );
       if ($no_run3) {
 
              system $line;
              INFO "sys:$line";
       }
 
       else {
 
              run3( $line, undef, \$out, \$err, { return_if_system_error => 1 } );
 
       }
 
       if ($err) {
 
              chomp($err);
 
              die $err;
 
       }
 
}
 
sub get_datetime {
 
       #get date/month
 
       my ( $min, $hr, $day, $mon, $yr ) = (localtime)[ 1, 2, 3, 4, 5, ];
 
       $yr += 1900;
       $mon += 1;
 
       my $today = sprintf( "%04d-%02d-%02d", $yr, $mon, $day );
 
       my $time = sprintf( "%02d%02d", $hr, $min );
 
       return ( $today, $time );
 
}
 
sub create_source_dir {
 
       my ( $date, $time ) = @_;
 
       #directory structure should be /yyyy-mm-dd/hhmm
 
       #make directory if it does not exist
 
       my $sourcedir = "$tempdir/$date$time";
 
       #if time directory exists then throw an error
 
       if ( -e $sourcedir ) {
 
              die "Sorry a source directory at this time point '$sourcedir' already exists have you another backup running?";
 
       }
 
       mkdir $sourcedir;
 
       return $sourcedir;
}
 
sub backup_databases {
 
       my ( $db_list, $dbuser, $dbpass, $dbhost) = @_;
 
       #loop through databases and backup
 
       foreach my $db ( @{$db_list} ) {
 
              my $cmd =
"mysqldump -u$dbuser -p$dbpass -h$dbhost --opt --single-transaction  --quick $db | gzip -c > $db.sql.gz";
 
              eval {
 
                     sysrun $cmd;
 
              };
 
              if ($@) {
 
                     chomp($@);
                     system "rm $db.sql.gz";
                     push @errors, "Problem backing up database '$db' ($@)";
 
                     next;
 
              }
 
              INFO "Backed up database '$db'";
 
       }
 
}
 
sub copy_to_remote {
 
       my ( $sourcepath, $date, $time ) = @_;
      
       $date=~s/\-/\//g;
 
       !-e $remotedir and make_path $remotedir;
      
       #copy directory into remote mounted dir
       INFO "Change to dir $remotedir";
      
       eval {
              !-e $remotedir and die "Remote directory $remotedir does not exist";
              chdir $remotedir;
              !-e $date and make_path $date;
              chdir $date;
              -e $time and die "Target directory $date/$time already exists";
              mkdir "$time" or die "Unable to create directory $time";
              chdir "$time";
              sysrun "cp -r $sourcepath/* .";
              INFO "Copy backup from $sourcepath to $remotedir/$date/$time";
              INFO "Remove temporary time dir $sourcepath";
        chdir $sourcepath;
        unlink glob "*.sql.gz" or ERROR "Unable to delete files in $sourcepath";
        rmdir $sourcepath or ERROR "Unable to remove temporary dir $sourcepath";
       };
       if ($@) {
 
              chomp($@);
 
              push @errors,
				"Problem backing up timepoint '$date/$time' to remote directory '$remotedir' ($@)";
       }
}
 
