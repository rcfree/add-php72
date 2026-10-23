#!perl

use File::Find;
use File::Path qw(rmtree);
use File::Copy qw(mv);
use Cwd;
use Date::Calc qw(Delta_YMD);
use Log::Log4perl qw(:easy);
Log::Log4perl->easy_init($DEBUG);
	
sub get_recursive_directories {
	my($dir)=shift;
	my @dirs = ();
	find(sub { 
	 	push @dirs, "$File::Find::name" if(-d $File::Find::name);
	}, $dir);
	@dirs;
}

sub get_date_time_from_filename {
	my $filename = shift;
	$match = $filename=~/(\d{4})\/(\d{2})\/(\d{2})\/(\d{4})$/;
	return (0,0,0,0) if(!$match);
	my($y,$m,$d,$t) = ($1,$2,$3,$4);
}

sub delta_from_file_date {
	my $today = shift;
	my $d = shift;
	my $m = shift;
	my $y = shift;
	my($todayY,$todayM,$todayD)=split("-",$today);
	
	my @delta = Delta_YMD($todayY,$todayM,$todayD,$y,$m,$d);
	
	return @delta,$d,$m,$y;
}

sub divide_dirs_into_age {
	my $today=shift;
	
	my @dirs = @{shift @_};
	my %older_than_xyears=();
	my %older_than_xmonths=();
	
	foreach my $dir(@dirs) {
		my($y,$m,$d,$t) = get_date_time_from_filename($dir);
		next if !$y;
	
		my @delta = delta_from_file_date($today,$d,$m,$y);
		
		$oldy = older_than_years(@delta);
		if($oldy) {
			if(!$older_than_xyears{$oldy}) {
				$older_than_xyears{$oldy}=[];
			}
			push @{$older_than_xyears{$oldy}},$dir;
		}
		
		next if $oldy;
		$oldm = older_than_months(@delta);
		if($oldm>1) {
			if(!$older_than_xmonths{$oldm}) {
				$older_than_xmonths{$oldm}={};
			}
			if(!$older_than_xmonths{$oldm}->{$d}) {
				$older_than_xmonths{$oldm}->{$d}=[];
			}
			push @{$older_than_xmonths{$oldm}->{$d}},$dir;
		}
		else {
			INFO "Leave directory $dir";
		}
	}	
	return (\%older_than_xyears,\%older_than_xmonths);
}

sub extract_dirs_to_delete_and_move {
	my $older_than_years = shift;
	my $older_than_months = shift;
	my @dirs_to_delete = ();
	my %dirs_to_move = ();
	foreach my $dirs(values %{$older_than_years}) {
		
		my @dirList = @{$dirs};
		my $moveDir = shift @dirList;
		
		$dirs_to_move{$moveDir} = determine_rename_to($moveDir);
		@dirs_to_delete=(@dirs_to_delete,@dirList);
	}
	foreach my $monthsOld(keys %{$older_than_months}) {
		if($monthsOld>=1 && $monthsOld<2) {
			my @daysInMonth = keys %{$older_than_months->{$monthsOld}};
			foreach my $dayInMonth(@daysInMonth) {
				my @dirs = @{$older_than_months->{$monthsOld}->{$dayInMonth}};
				my $moveDir = shift @dirs;

				$dirs_to_move{$moveDir}=determine_rename_to($moveDir);
				@dirs_to_delete=(@dirs_to_delete,@dirs)
			}
		}
		else {
			my @daysInMonth = keys %{$older_than_months->{$monthsOld}};
			my $moveDir = shift @{$older_than_months->{$monthsOld}->{$daysInMonth[0]}};
			$dirs_to_move{$moveDir}=determine_rename_to($moveDir);
			
			foreach my $dayInMonth(@daysInMonth) {
				my @dirs = @{$older_than_months->{$monthsOld}->{$dayInMonth}};
				
				@dirs_to_delete=(@dirs_to_delete,@dirs)
			}
		}
	}
	
	return (\@dirs_to_delete,\%dirs_to_move);
}

sub older_than_years {
	my ($deltaY,$deltaM,$deltaD) = @_;
	$noOfYears = $deltaY*-1;
	if($deltaM==0 || $deltaM==-1) {
		if($deltaD<0) {
			return $noOfYears;
		}
	}
	elsif($deltaM<=-1) {
		return $noOfYears;
	}
	$noOfYears--;
	return 0 if($noOfYears==-1);
	return $noOfYears;
}

sub older_than_months {
	my ($deltaY,$deltaM,$deltaD) = @_;
	if($deltaY==-1 && $deltaM>0) {
		$noOfMonths = 12 - $deltaM;
		if($deltaD>0) {
			$noOfMonths--;
		}
		return $noOfMonths;
	}
	$noOfMonths = $deltaM*-1;
	if($deltaD<=0) {
		return $noOfMonths;
	}
	$noOfMonths--;
	return 0 if($noOfMonths==-1);
	return $noOfMonths;
}

sub determine_rename_to {
	my $filename = shift;
	my($y,$m,$d,$t) = get_date_time_from_filename($filename);
	return $y."-".$m."-".$d;
}

sub roll_backups {
	my ($today,$backupDir,$archiveDir) = @_;
	INFO "Roll backups in '$backupDir' and archive files in '$archiveDir' for $today";
	
	my @dirs = get_recursive_directories($backupDir);
	
	my($yold,$mold)= divide_dirs_into_age($today,\@dirs);
	INFO "yold:".Dumper($yold);
	INFO "mold:".Dumper($mold);
	my($delList,$moveMap) = extract_dirs_to_delete_and_move($yold,$mold);
	
	foreach my $moveFrom(keys %{$moveMap}) {
		my $moveTo = $archiveDir."/".$moveMap->{$moveFrom};
		INFO "Archive $moveFrom to $moveTo";
		if(-e $moveTo) {
			WARN "Unable to move '$moveFrom' to '$moveTo' as it already exists";
			next;
		}
		mv($moveFrom, $moveTo) or die "Problem - unable to move $moveFrom to ".$moveTo;
		$parent = clean_parent($moveFrom);
		clean_parent($parent);
	}
	foreach my $delItem (@{$delList}) {
		INFO "Remove $delItem";
		next if(!-e $delItem);
		rmtree($delItem) or die "Unable to remove $delItem $@";
		$parent = clean_parent($delItem);
		clean_parent($parent);
	}
}
sub clean_parent {
	my ($delItem)=shift;
	my @temp_dirs = split("/",$delItem);
	my @parentDirs = @temp_dirs[0..$#temp_dirs-1];
	my $parentDir = join("/",@parentDirs);
	#INFO "Parent dir $parentDir";
	return $parentDir if(!-e $parentDir);
		
	my @files = <$parentDir/*>;
	
	if(scalar(@files)==0) {
		INFO "Parent dir $parentDir removed - has no files";
		rmtree($parentDir) or die "Unable to remove $parentDir $@";
	}
	
	return $parentDir;
}
1;
