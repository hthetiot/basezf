#!/usr/bin/perl
# Fast script to get all test methods in a PHP test class.
# It returns only methods that are not inside comment blocks.
#
# Syntax: getTests.pl fileName.php [testPrefix]
#
# Without testPrefix will return all public methods
#
# $Rev: 2 $
#
# $LastChangedDate: 2009-03-06 13:11:06 +0200 (V, 06 mar. 2009) $
#

my ($fileName, $testPrefix) = @ARGV;

open my ($FILE), "<", $fileName or die 'Cannot open ' . $fileName .' for reading';

while(<$FILE>) {
    # begin of a comment
    if (/\/\*(?!\/\*)/) {
        $comment = 1;
        next;
    }

    # end of a comment
    if (/\*\//) {
        $comment = 0;
    }

    # must be a public function
    if (!$comment) {
        if (/^\s*public function ($testPrefix\w*)/ or /^\s*function ($testPrefix\w*)/) {
            print $1 . "\n";
        }
    }
}

#EOF#
