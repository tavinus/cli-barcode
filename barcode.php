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

define("_BC_VERSION",     "1.0.7");

# default padding for cli messages
define("_BC_PADDING",      30);
define("_BC_HELP_NEWLINE", "\n                               ");

$verbose = false;
$quiet   = false;

$encoding = null;
$format   = null;

$bc_string = null;
$bc_file   = null;

$width     = 2;
$height    = 30;
$color     = '#000000';

#$_defPermission = $false; # to be implemented
#$_defGroup = $false;      # to be implemented

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

# Converts Hex Color to array(r, g, b, [a])
function hexToRgb($hex) {
   $hex      = str_replace('#', '', $hex);
   $length   = strlen($hex);
   $rgb[] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
   $rgb[] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
   $rgb[] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
   return $rgb;
}


/////////////////// PRINT HELP

function getVersionString($suffix='') {
    return "PHP-CLI Barcode v"._BC_VERSION.$suffix;
}

function printVersion($suffix='') {
    echo getVersionString($suffix)."\n";
}

// prints help information
function print_help($getopt) {
    printVersion();
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


/////////////////// CREATE SH SCRIPT

// creates a shell script named barcode that will run this script
// from anywhere on the system. Assumes barcode.php is running
// on its final installation location
function create_bash_script() {
    $error = true;
    $bc_path = __FILE__;
    $bash_path = dirname($bc_path) . DIRECTORY_SEPARATOR . "barcode";

    $bash_script = <<<EOF
#!/usr/bin/env sh

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


/////////////////// GETOPT STARTS

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use Ulrichsg\Getopt\Argument;

// check if encoding callback
function isEncoding($enc=null) {
    global $encodings_list;
    return in_array(strtoupper($enc), $encodings_list);
}

// check if format callback
function isFormat($format=null) {
    global $formats_list;
    return in_array(strtoupper($format), $formats_list);
}

// check if empty callback
function not_empty($str) {
    return (!empty($str));
}

// check if empty callback
function isHexColor($str) {
    $str = str_replace('#', '', $str);
    if (! ctype_alnum($str) || (strlen($str) !== 6 && strlen($str) !== 3)) return false;
    return true;
}

function checkQuietExit($exitStat=0) {
    global $quiet;
    if ($quiet) ob_end_clean();
    exit($exitStat);
}

// define and configure options
$getopt = new Getopt(array(
    (new Option('e', 'encoding', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Barcode encoding type selection, listed below')
        ->setArgument(new Argument(null, 'isEncoding', 'bar-type')),
    (new Option('f', 'format', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Output format for the barcode, listed below')
        ->setArgument(new Argument(null, 'isFormat', 'file-type')),
    (new Option('w', 'width', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Width factor for bars to make wider, defaults to 2')
        ->setArgument(new Argument(2, 'is_numeric', 'points')),
    (new Option('h', 'height', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Total height of the barcode, defaults to 30')
        ->setArgument(new Argument(30, 'is_numeric', 'points')),
    (new Option('c', 'color', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Hex code of the foreground color, defaults to black'._BC_HELP_NEWLINE."Eg. -c 54863b, or -c '#000'")
        ->setArgument(new Argument('#000000', 'isHexColor', 'hex-color')),
    (new Option('v', 'verbose'))
        ->setDescription('Prints verbose information to screen'._BC_HELP_NEWLINE."Use twice for timestamp")
        ->setDefaultValue(false),
    (new Option('q', 'quiet'))
        ->setDescription('Supress all messages, even errors')
        ->setDefaultValue(false),
    (new Option(null, 'help'))
        ->setDescription('Help Information, including encodings and formats'),
    (new Option(null, 'version'))
        ->setDescription('Display version information and exits'),
    (new Option(null, 'create-bash'))
        ->setDescription('Creates a shell script named \'barcode\' that can call this script')
));

$getopt->setBanner("Usage: barcode -e <encoding> -f <output_format> [options] <barcode string> <output file>\n");

try {
    $getopt->parse();

    if ($getopt['version']) {
        printVersion();
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
    $color     = '#'.str_replace('#', '', $getopt['color']);

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

// check output directory
$tgtDir = dirname($bc_file);
if (! is_dir($tgtDir)) {
    echo "Error: Could not locate target directory!\nTarget Dir: $tgtDir\n";
    exit(1);
}

// Match case
$encoding = strtoupper($encoding);
$format   = strtoupper($format);



/////////////////// QUIET EXECUTION STARTS

// From here on use checkQuietExit() to Exit
if ($quiet) {
    ob_start();
}

/////////////////// GETOPT ENDS


/////////////////// PRINT VERBOSE INFO

vPrint(getVersionString(" - Verbose Execution"));
vPrint(array("Output File", "$bc_file"));
vPrint(array("Barcode String", "$bc_string"));
vPrint(array("Barcode Encoding", "$encoding"));
vPrint(array("Output Format", "$format"));
vPrint(array("Width Factor", "$width"));
vPrint(array("Height of Barcode", "$height"));
vPrint(array("Hex Color", "$color"));

function vPrint($input) {
    global $verbose;
    if ($verbose) { 
        if ($verbose > 1) echo date(DATE_ATOM) . " | ";
        if (is_array($input) && count($input) > 1) {
            printf("%-17s : %-30s\n", $input[0], $input[1]);
        } else if (is_string($input)) {
            echo "$input\n";
        }
    }
}


/////////////////// CREATE BARCODE STARTS

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
    $color     = hexToRgb($color);
    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
} else if ($format === 'JPG') {
    $color     = hexToRgb($color);
    $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
} else if ($format === 'HTML') {
    $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
} else {
    echo "Error: Invalid taget format: $format\n";
    checkQuietExit(1);
}

// generate de barcode
try {
    $bc_data  = $generator->getBarcode($bc_string, $bc_type, $width, $height, $color);    
} catch (Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
    checkQuietExit(1);
}

// save to file
if (@file_put_contents($bc_file, $bc_data) === false) {
    echo "Error: could not save file $bc_file!\n";
    checkQuietExit(1);
}

// set permissions and group
#chmod($bc_file, $_defPermission);
#chgrp($bc_file, $_defGroup);

/////////////////// CREATE BARCODE ENDS


/////////////////// PRINT VERBOSE INFO ENDS

vprint(array("Final Status", "Success"));
checkQuietExit(0);

?>
