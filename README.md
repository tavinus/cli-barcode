# cli-barcode PHP
Generates awesome barcodes from CLI using PHP  
This script uses @picqer's [php-barcode-generator](https://github.com/picqer/php-barcode-generator) to generate barcodes from the command line.  
For command line parsing @ulrichsg's [getopt-php](https://github.com/ulrichsg/getopt-php) is used.  
  
## Generating barcodes
Usage is pretty straight forward.  
##### There are 4 required parameters  
1. Encoding (barcode type)  
2. Output format (png, jpg, svg, html)  
3. Barcode string (will become the barcode)  
4. Output file (where to save it)  

##### Apart from that you get a few optional parameters
* Width factor for the bars, defaults to 2
* Height of the bars, defaults to 30
* Color of the bars, defaults to '#000000' (black)
  
I find the default settings for these very optimal, since there is usually no big penalty on resizing the barcodes generated.  
The encodings and output formats are case-insenstive.  

##### Important Notes
From [v1.0.6](https://github.com/tavinus/cli-barcode/releases/tag/1.0.6) parameters and options can be passed in any order,  
as long as the `Barcode String` comes before the `Output File`.  
This requires my patched version of `getopt-php`.  
  
## Example Runs
#### Blue Colored `CODE_128_C` SVG with "123123123123"
```
$ barcode -v -c '#0030ff' -e CODE_128_C -f SVG "123123123123" $HOME/teste.svg 
PHP-CLI Barcode v1.0.6 - Verbose Execution
Output File       : /home/guneves/teste.svg       
Barcode String    : 123123123123                  
Barcode Encoding  : CODE_128_C                    
Output Format     : SVG                           
Width Factor      : 2                             
Height of Barcode : 30                            
Hex Color         : #0030ff                       
Final Status      : Success
```
#### `CODE_39` PNG with "A GREAT BAR" and custom Width Factor and Height
```
$ barcode -vv -e CODE_39 -f PNG "A GREAT BAR" $HOME/test.png 
2017-05-20T06:10:44-03:00 | PHP-CLI Barcode v1.0.6 - Verbose Execution
2017-05-20T06:10:44-03:00 | Output File       : /home/guneves/test.png        
2017-05-20T06:10:44-03:00 | Barcode String    : A GREAT BAR                   
2017-05-20T06:10:44-03:00 | Barcode Encoding  : CODE_39                       
2017-05-20T06:10:44-03:00 | Output Format     : PNG                           
2017-05-20T06:10:44-03:00 | Width Factor      : 2                             
2017-05-20T06:10:44-03:00 | Height of Barcode : 30                            
2017-05-20T06:10:44-03:00 | Hex Color         : #000000                       
2017-05-20T06:10:44-03:00 | Final Status      : Success
```
## Help Information from cli
```
$ barcode --help
PHP-CLI Barcode v1.0.6
Usage: barcode -e <encoding> -f <output_format> [options] <barcode string> <output file>
Options:
  -e, --encoding <bar-type>    Barcode encoding type selection, listed below
  -f, --format <file-type>     Output format for the barcode, listed below
  -w, --width <points>         Width factor for bars to make wider, defaults to 2
  -h, --height <points>        Total height of the barcode, defaults to 30
  -c, --color <hex-color>      Hex code of the foreground color, defaults to black
                               Eg. -c 54863b, or -c '#000'
  -v, --verbose                Prints verbose information to screen
                               Use twice for timestamp
  -q, --quiet                  Supress all messages, even errors
  --help                       Help Information, including encodings and formats
  --version                    Display version information and exits
  --create-bash                Creates a shell script named 'barcode' that can call this script

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


## How to install using `Make`
The vendor folder is included so we can just download this package and run it.  
I have also made some patches upstream to both `getopt-php` and `php-barcode-generator`.  
  
You can download the zip or tar ball and put the cli-barcode folder where you want it to be installed.  
  
### Or use git to clone:
```
git clone https://github.com/tavinus/cli-barcode.git $HOME/cli-barcode # <= Replace TARGET
cd $HOME/cli-barcode # <= Replace TARGET
```

Then you can run:  
```
./barcode.php --create-bash
``` 
To recreate the `barcode` file with the full path to your installation. 
  
At this point you can run:
```
sudo make install
``` 
To copy the shell executable to `/usr/local/bin/barcode`.  


