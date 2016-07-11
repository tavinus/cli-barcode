#!/usr/bin/env php
<?php
##########################################################################
# Gustavo Arnosti Neves - 2016 Jul 11
# guneves < a t > gmail < d o t > com
#
# PHP cli-barcode-generator
#
# Most of the work here is for option / argument parsing.
# Picqer's barcode framework makes it east on the bar coding generation.
# While ulrichsg's getopt-php helps with the cli part.
#
##########################################################################

require_once 'vendor/autoload.php';

define("_BC_VERSION",    "1.0.2");

       # permission to set to barcode files
define("_BC_PERMISSION",  0644);
       # group to set to barcode files (disabled at bot)
define("_BC_SYSGROUP",   "yourGrpHere");
       # default padding for barcode
define("_BC_PADDING",     30); 


$verbose = false;
$quiet   = false;

$encoding = null;
$format   = null;

$bc_string = null;
$bc_file   = null;

$width     = 2;
$height    = 30;
$color     = '#000000';

$encodings_list = array(
    'CODE_39',
    'CODE_39_CHECKSUM',
    'CODE_39E',
    'CODE_39E_CHECKSUM',
    'CODE_93',
    'STANDARD_2_5',
    'STANDARD_2_5_CHECKSUM',
    'INTERLEAVED_2_5',
    'INTERLEAVED_2_5_CHECKSUM',
    'CODE_128',
    'CODE_128_A',
    'CODE_128_B',
    'CODE_128_C',
    'EAN_2',
    'EAN_5',
    'EAN_8',
    'EAN_13',
    'UPC_A',
    'UPC_E',
    'MSI',
    'MSI_CHECKSUM',
    'POSTNET',
    'PLANET',
    'RMS4CC',
    'KIX',
    'IMB',
    'CODABAR',
    'CODE_11',
    'PHARMA_CODE',
    'PHARMA_CODE_TWO_TRACKS'
);
sort($encodings_list);

$formats_list = array(
    'SVG',
    'PNG',
    'JPG',
    'HTML'
);
sort($formats_list);


/////////////////// GETOPT STARTS

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use Ulrichsg\Getopt\Argument;

// define and configure options
$getopt = new Getopt(array(
    (new Option('e', 'encoding', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Barcode encoding type selection')
        ->setValidation(function($value) {
            global $encodings_list;
            return in_array(strtoupper($value), $encodings_list);
        })
        ->setArgument(new Argument(null, null, 'bar-type')),
    (new Option('f', 'format', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Output format for the barcode')
        ->setValidation(function($value, $formats_list) {
            global $formats_list;
            return in_array(strtoupper($value), $formats_list);
        })
        ->setArgument(new Argument(null, null, 'file-type')),
    (new Option('w', 'width', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Width factor for bars to make wider, defaults to 2')
        ->setArgument(new Argument(2, 'is_numeric', 'points')),
    (new Option('h', 'height', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Total height of the barcode, defaults to 30')
        ->setArgument(new Argument(30, 'is_numeric', 'points')),
    (new Option('c', 'color', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Hex code of the foreground color, defaults to black')
        ->setArgument(new Argument('#000000', 'not_empty', 'hex-color')),
    (new Option('v', 'verbose'))
        ->setDescription('Display extra information')
        ->setDefaultValue(false),
    (new Option('q', 'quiet'))
        ->setDescription('Supress all messages')
        ->setDefaultValue(false),
    (new Option(null, 'help'))
        ->setDescription('Help Information, including encodings and formats'),
    (new Option(null, 'version'))
        ->setDescription('Display version information and exits'),
    (new Option(null, 'create-bash'))
        ->setDescription('Creates a bash script named barcode that can call this script')
));

$getopt->setBanner("Usage: barcode -e <encoding> -f <output_format> [options] <barcode string> <output file>\n");

try {
    $getopt->parse();

    if ($getopt['version']) {
        echo "PHP-CLI Barcode v"._BC_VERSION."\n";
        exit(0);
    }

    if ($getopt['help']) {
        print_help($getopt);
        exit(0);
    }

    if ($getopt['create-bash']) {
        create_bash_script();
        exit(1);
    }

    $verbose   = $getopt['verbose'];
    $quiet     = $getopt['quiet'];
    
    $encoding  = $getopt['encoding'];
    $format    = $getopt['format'];
    
    $width     = $getopt['width'];
    $height    = $getopt['height'];
    $color     = $getopt['color'];

    $bc_string = $getopt->getOperand(0);
    $bc_file   = $getopt->getOperand(1);
    
} catch (UnexpectedValueException $e) {
    echo "Error: ".$e->getMessage()."\n";
    echo $getopt->getHelpText(_BC_PADDING);
    exit(1);
}

// check if we can proceed
if (empty($encoding) || empty($format) || empty($bc_string) || empty($bc_file)) {
    echo "Error: Invalid parameters or options.\n";
    echo $getopt->getHelpText(_BC_PADDING);
    exit(1);
}

// Match case
$encoding = strtoupper($encoding);
$format   = strtoupper($format);


/////////////////// GETOPT ENDS


/////////////////// CREATE BARCODE

// creates a bash script named barcode that will run this script
// from anywhere on the system. Assumes barcode.php is running
// on its final installation location
function create_bash_script() {
    $error = true;
    $bc_path = __FILE__;
    $bash_path = dirname($bc_path) . DIRECTORY_SEPARATOR . "barcode";

	$bash_script = <<<EOF
#!/usr/bin/env bash

##################################################
# Gustavo Arnosti Neves - 2016 Jul 11
# Simple bash script for global barcode executable
# PHP cli-barcode-generator
#
# Please run "./barcode.php --create-bash" to update this script
# You can then use sudo make install to copy it to /usr/local/bin
#
# This won't work on windows
#

BARCODE_LOCATION="$bc_path"   # enter full path here
/usr/bin/env php "\$BARCODE_LOCATION" "\$@"

EOF;

    if (file_exists($bash_path)) {
        unlink($bash_path) or die("Could not remove old barcode script, are you running from it?");
    }

    $error = file_put_contents($bash_path, $bash_script) === true ? false : true;
    $error = chmod($bash_path, 0755) === true ? false : true;
    
    if ($error) {
        echo "\nAn error was detected during the process.\n";
        echo "Please check for permissions and try again.\n\n";
        exit(2);
    }
    echo "\nThe file \"$bash_path\" was successfully created.\n";
    echo "You may perform a system install to /usr/local/bin by issuing\n"; 
    echo "the command \"sudo make install\"\n\n";
    exit(0);
}

// get actual barcode type string
$bc_type  = constant('Picqer\Barcode\BarcodeGenerator::TYPE_'.$encoding);

// add trailling zero if odd digits on Code128C
$bc_string = strlen($bc_string) % 2 == 0 && $encoding === 'CODE_128_C'? 
    $bc_string : '0'.$bc_string;

// create appropriate generator
$generator = null;
if ($format === 'SVG') {
    $generator = new Picqer\Barcode\BarcodeGeneratorSVG();
} else if ($format === 'PNG') {
    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
} else if ($format === 'JPG') {
    $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
} else if ($format === 'HTML') {
    $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
} else {
    exit(1);
}

// generate de barcode
$bc_data  = $generator->getBarcode($bc_string, $bc_type, $width, $height, $color);

// save to file
if (file_put_contents($bc_file, $bc_data) === false) {
    echo "Error: could not save file $bc_file!\n";
    exit(1);
}

// set permissions and group
chmod($bc_file, _BC_PERMISSION);
#chgrp($bc_file, _BC_SYSGROUP);


// prints help information
function print_help($getopt) {
    global $encodings_list;
    global $formats_list;
    
    echo $getopt->getHelpText(_BC_PADDING);
    echo "\nRequired Options and Parameters:\n";
    echo "    -e <encoding>\n";
    echo "    -f <output format>\n";
    echo "    <input string>\n";
    echo "    <output file>\n";
    echo "\nOutput Formats:\n";
    foreach($formats_list as $for) {
        echo "    $for\n";
    }
    echo "\nEncodings:\n";
    foreach($encodings_list as $enc) {
        echo "    $enc\n";
    }
    echo "\nExamples:\n";
    echo "    barcode -f HTML -e CODE_39 \"1234567890\" \"/tmp/1234567890.html\"\n";
    echo "    barcode -e CODE_128 -f PNG -c \"#888\" -w 3 -h 50 \"AGREATBAR\" \"/tmp/AGREATBAR.png\"\n";
    echo "    barcode \"1234567890\" \"/tmp/mybar.svg\" --encoding EAN_13 --format SVG\n";
}

// check if empty (callback)
function not_empty($str) {
    return (!empty($str));
}


// done
exit(0);

?>