<?
// rmfiles.php - finds the files in a specific directory and 
//               its subdirectories that have been accessed (or modified or created)
//               and optionnaly deletes them
// (c) 2008-2019 Cartman67@kreator.org

// This is DEMO code, use it at your own risk
// If you understood, remove or comment out the line below:
exit(-1);

// You need to set the correct time zone (for example Europe/Paris) in php.ini

// get command line arguments
if ( PHP_SAPI=="cli")
    { 
    define("PHPEOL","\n");
    if ( ($argc<3) || ($argc>4) )
        ErrorMessage(6);
    $dir=$argv[1];
    if ( $argc==4 )
        $targetdate=$argv[2]." ".$argv[3];
        else
        $targetdate=$argv[2];
    }
    else
    ErrorMessage(5); 
if ( !is_dir($dir) )
    ErrorMessage(1);

// directory to scan
//$dir="/usr/local/include";

// file date to find
// $targetdate="12/07/2019 14:27:23";

// range of timestamp in seconds, before and after the given targetdate
$rangetimestamp=60;

// select which file time you want to be checked:
// 1: modification time
// 2: last access time
// 3: creation time
$filetimetype=3;

// DELETES files if set to true
$deletefoundfiles=false;
    
// date string to unix timestamp
if ( ($targettimestamp=mktime(substr($targetdate,11,2),substr($targetdate,14,2)
        ,substr($targetdate,17,2),substr($targetdate,3,2),substr($targetdate,0,2)
        ,substr($targetdate,6,4)))===false )
    ErrorMessage(6);

// returns the wanted timestamp of the file
function GetFileTimestamp($file,$filetimetype)
    {
    switch( $filetimetype )
        {
        case 1: return(filemtime($file)); 
        case 2: return(fileatime($file));
        case 3: return(filectime($file));
        default: ErrorMessage(2);
        }
    }

// error handler
function ErrorMessage($error)
    {
    $errormessages=array("Not a directory","GetFileTimestamp: unknown filetimetype"
            ,"ScanDirForFiles: cannot open dir","ScanDirForFiles: cannot delete the file"
            ,"Only to be used from command line"
            ,"Usage: remove-file-by-date.php directorytoscan dd/mm/yyyy hh:mm:ss ".PHPEOL."   will scan for files that have been created during the given timestamp +/- 60 seconds"
            ,"Error in mktime, check your date and time formats" );
    if ( ($error>0) and ($error<=count($errormessages)) )
        echo $errormessages[$error-1];
        else
        echo "Unknown error code";
    echo PHPEOL;
    exit(-$error);
    }

// scan dir
function ScanDirForFiles($dir)
    {
    global $targettimestamp,$rangetimestamp,$deletefoundfiles,$filetimetype;

    if ( is_link($dir) )
        return;
    if ( $handle=opendir($dir) )
        {
        while( ($file=readdir($handle))!==false ) 
            {
            $fullfilename=$dir."/".$file;
            if ( !is_dir($fullfilename) )
                {
                if ( is_file($fullfilename) )
                    {
                    $filetimestamp=GetFileTimestamp($fullfilename,$filetimetype);
                    if ( ($filetimestamp>=($targettimestamp-$rangetimestamp)) 
                                && ($filetimestamp<=($targettimestamp+$rangetimestamp)) )
                        {
                        echo $fullfilename." ".strftime("%d/%m/%Y %H:%M:%S",$filetimestamp).PHPEOL;
                        if ( $deletefoundfiles )
                            {
                            if ( unlink($fullfilename)===false )
                                ErrorMessage(4);
                                else
                                echo "   Deleted".PHPEOL;
                            }
                        }
                    }
                }
                else
                {
                if ( ($file!=".") && ($file!="..") )
                    ScanDirForFiles($fullfilename);
                }
            }
        closedir($handle);
        }
/*        else
        ErrorMessage(3);*/
    }
    
// main
echo "Scanning ".$dir." for files dating from ".$targetdate." +/-".$rangetimestamp." seconds".PHPEOL;
ScanDirForFiles($dir);
?> 
