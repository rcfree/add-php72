#!/usr/bin/perl
 
use strict;
use warnings;
use Test;
use Test::More;
use Data::Dumper qw(Dumper);

require "lib/rollbackup.pm";
BEGIN { plan tests => 19 }

my $today = "2014-10-10";

my @test_files = (
    "backup/2011/01/01/0900",
    "backup/2012/03/01/0900",
    "backup/2012/03/01/1000",
    "backup/2012/03/02/0900",
    "backup/2013/05/01/0900",
    "backup/2014/01/01/0900",
    "backup/2014/02/01/0900",
    "backup/2014/03/01/0900",
    "backup/2014/04/01/0900",
    "backup/2014/05/01/0900",
    "backup/2014/06/01/0900",
    "backup/2014/07/01/0900",
    "backup/2014/08/01/0900",
    "backup/2014/09/01/0900",
    "backup/2014/09/01/1000",
    "backup/2014/09/02/0900",
    "backup/2014/09/02/1000",
    "backup/2014/09/02/1100",
    "backup/2014/09/03/0900",
    "backup/2014/10/01/0900",
    "backup/2014/10/01/1000",
    "backup/2014/10/02/0900",
    "archive/2014-10-01",
    "backup",
    "backup/2014/10/01/1100",
    "backup/2014/10/01/1200",
    "backup/2014/10/01/1300",
    "archive/2014/10/01"
);

my($obsYold,$obsMold)= divide_dirs_into_age($today,\@test_files);

my $expYold = {
          '1' => [
                   'backup/2013/05/01/0900'
                 ],
          '3' => [
                   'backup/2011/01/01/0900'
                 ],
          '2' => [
                   'backup/2012/03/01/0900',
                   'backup/2012/03/01/1000',
                   'backup/2012/03/02/0900'
                 ]
        };
my $expMold = {
          '6' => {
                   '01' => [
                             'backup/2014/04/01/0900'
                           ]
                 },
          '3' => {
                   '01' => [
                             'backup/2014/07/01/0900'
                           ]
                 },
          '7' => {
                   '01' => [
                             'backup/2014/03/01/0900'
                           ]
                 },
          '9' => {
                   '01' => [
                             'backup/2014/01/01/0900'
                           ]
                 },
          '2' => {
                   '01' => [
                             'backup/2014/08/01/0900'
                           ]
                 },
          '8' => {
                   '01' => [
                             'backup/2014/02/01/0900'
                           ]
                 },
          '4' => {
                   '01' => [
                             'backup/2014/06/01/0900'
                           ]
                 },
          '1' => {
                   '01' => [
                             'backup/2014/09/01/0900',
                             'backup/2014/09/01/1000'
                           ],
                   '03' => [
                             'backup/2014/09/03/0900'
                           ],
                   '02' => [
                             'backup/2014/09/02/0900',
                             'backup/2014/09/02/1000',
                             'backup/2014/09/02/1100'
                           ]
                 },
          '5' => {
                   '01' => [
                             'backup/2014/05/01/0900'
                           ]
                 }
        };

is_deeply($obsYold,$expYold);
is_deeply($obsMold,$expMold);

my($obsDel,$obsMove) = extract_dirs_to_delete_and_move($obsYold,$obsMold);

my $expDel = [
          'backup/2012/03/01/1000',
          'backup/2012/03/02/0900',
          'backup/2014/09/01/1000',
          'backup/2014/09/02/1000',
          'backup/2014/09/02/1100'
        ];
my $expMove = {
          'backup/2014/03/01/0900' => '2014-03-01',
          'backup/2014/04/01/0900' => '2014-04-01',
          'backup/2013/05/01/0900' => '2013-05-01',
          'backup/2014/06/01/0900' => '2014-06-01',
          'backup/2014/09/01/0900' => '2014-09-01',
          'backup/2012/03/01/0900' => '2012-03-01',
          'backup/2011/01/01/0900' => '2011-01-01',
          'backup/2014/05/01/0900' => '2014-05-01',
          'backup/2014/07/01/0900' => '2014-07-01',
          'backup/2014/01/01/0900' => '2014-01-01',
          'backup/2014/09/02/0900' => '2014-09-02',
          'backup/2014/09/03/0900' => '2014-09-03',
          'backup/2014/02/01/0900' => '2014-02-01',
          'backup/2014/08/01/0900' => '2014-08-01'
        };

is_deeply($expDel,$obsDel);
is_deeply($expMove,$obsMove);

is(older_than_years(-1,10,7),0);
is(older_than_years(-1,0,7),0);
is(older_than_years(-1,-1,7),0);
is(older_than_years(-1,-1,7),0);
is(older_than_years(-1,0,-2),1);
is(older_than_years(-1,0,0),0);
is(older_than_years(-1,3,-1),0);
is(older_than_years(-2,3,-1),1);
is(older_than_years(-2,-2,5),2);
is(older_than_years(-2,0,0),1);
is(older_than_years(-2,0,-1),2);
is(older_than_months(0,-1,7),0); #2014-01-15 - 2014-02-08
is(older_than_months(0,-1,-2),1); #2014-01-15 - 2014-02-17
is(older_than_months(0,0,-1),0); #2014-01-15 - 2014-01-16
is(older_than_months(-2,0,-1),0); #2014-01-15 - 2014-01-16
is(older_than_months(-1,8,0),4); #2014-10-11 - 2015-02-11
is(older_than_months(-1,8,5),3); #2014-10-11 - 2015-02-06
is(older_than_months(0,-8,5),7); #2014-02-11 - 2014-10-06
is(older_than_months(0,-8,0),8); #2014-02-11 - 2014-10-06
is(older_than_months(-1,1,0),11); #2014-02-01 - 2015-01-01