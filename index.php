<?php



include_once("inc/loader.php");






/**
 * SyncNextCloud -> Scans nextcloud sync folders and copies new files to 
 *                  $data["nextcloud"]["syncdir"]
 */
$scan_nextcloud = false;
if($scan_nextcloud){
    SyncNextCloud();
}
/**
 * ScanDatumsSortierte -> scans /share/Datumsortierte_Bilder/ and adds filepath and md5 to table datumsortiert
 */
$scan_datumssortierte = false;
if($scan_datumssortierte) {
    ScanDatumsSortierte();
}

/**
 * 
 * 
 */
$phockup_sync_autosort = false;
if($phockup_sync_autosort){
    phockupSortAUTOSORT();
}

/**
 *  Crawles all $data["scandirs"], pushed files into table fullindex, calculates md5 and sizes in second loop
 *  Sets size of all empty files to -1
 */
$sharefiles_index = false;
 if($sharefiles_index){
    IndexShareFiles();
}














if(isset($data["debug"]))echo print_r($data["debug"],true);
