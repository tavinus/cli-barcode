# cli-barcode PHP
Generates awesome barcodes from CLI using PHP  
This script uses @picqer's [php-barcode-generator](https://github.com/picqer/php-barcode-generator) to generate barcodes from the command line.  
For command line parsing @ulrichsg's [getopt-php](https://github.com/ulrichsg/getopt-php) is used.  
  
The credits should go mostly to them, since this is a very simple script that uses what they have made.  

## How to install
The vendor folder is included since I want to be able to just download this package and install it.  
  
You should download the zip or tarball and extract it / move it to where you want it to be instaled.  

Then you can run:  
```
./barcode.php --create-bash
``` 
To recreate the `barcode` file with the full path to your installation. 
  
At this point you can run:
```
sudo make install
``` 
To copy the bash executable to `/usr/local/bin`.  
You will need to have `make` installed for this.  
  
## Generating barcodes
Usage is pretty straight forward.  
#####There are 4 required parameters  
1. Encoding (barcode type)  
2. Output format (jpg, svg, etc)  
3. Barcode string (will become the barcode)  
4. Output file (where to save it)  

#####Apart from that you get a few optional parameters
* Width factor for the bars (defaults to 2)
* Height of the bars (defaults to 30)
* Color of the bars (defaults to black)
  
I find the default settings for these very optimal, since there is usually no problem on resising the barcodes generated.

## Help Information from cli
```
$ barcode --help
Usage: barcode -e <encoding> -f <output_format> [options] <barcode string> <output file>
Options:
  -e, --encoding <bar-type>    Barcode encoding type selection
  -f, --format <file-type>     Output format for the barcode
  -w, --width <points>         Width factor for bars to make wider, defaults to 2
  -h, --height <points>        Total height of the barcode, defaults to 30
  -c, --color <hex-color>      Hex code of the foreground color, defaults to black
  -v, --verbose                Display extra information
  -q, --quiet                  Supress all messages
  --help                       Help Information, including encodings and formats
  --version                    Display version information and exits

Required Options and Parameters:
    -e <encoding>
    -f <output format>
    <input string>
    <output file>

Output Formats:
    HTML
    JPG
    PNG
    SVG

Encodings:
    CODABAR
    CODE_11
    CODE_128
    CODE_128_A
    CODE_128_B
    CODE_128_C
    CODE_39
    CODE_39E
    CODE_39E_CHECKSUM
    CODE_39_CHECKSUM
    CODE_93
    EAN_13
    EAN_2
    EAN_5
    EAN_8
    IMB
    INTERLEAVED_2_5
    INTERLEAVED_2_5_CHECKSUM
    KIX
    MSI
    MSI_CHECKSUM
    PHARMA_CODE
    PHARMA_CODE_TWO_TRACKS
    PLANET
    POSTNET
    RMS4CC
    STANDARD_2_5
    STANDARD_2_5_CHECKSUM
    UPC_A
    UPC_E

Examples:
    barcode -f HTML -e CODE_39 "1234567890" "/tmp/1234567890.html"
    barcode -e CODE_128 -f PNG -c "#888" -w 3 -h 50 "AGREATBAR" "/tmp/AGREATBAR.png"
    barcode "1234567890" "/tmp/mybar.svg" --encoding EAN_13 --format SVG
```



