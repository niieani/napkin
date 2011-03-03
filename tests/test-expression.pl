#!/usr/bin/perl
use warnings;
#print "Hello world!\n";
use YAPE::Regex::Explain;
print YAPE::Regex::Explain->new('%\(([a-zA-Z_]\w*)\)')->explain;
#print YAPE::Regex::Explain->new('(?R)')->explain;

