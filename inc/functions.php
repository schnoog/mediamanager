<?php




function info_cli(){
    global $data;
    $out[] = "M E D I A M A N A G E R";
    $out[] = "";
    $out[] = "Folder Setup";
    $out[] = "-Autosort folder ("  . '$data["phockup_syncdir"]' . ")";
    $out[] = "  ->" . $data["phockup_syncdir"] . " - Don't move any files manually into or out of this folder";   
    $out[] = "-Basic Folders (".'$data["scandirs"]'.") which are scanned (and captured in fullindex):";
    $dl ="  ->";
    foreach($data["scandirs"] as $sd){
        //$out[] = "  ->" . $data["basepath"] . $sd;
        if(strlen($dl) > 8) $dl .= " / ";
//        if(strlen($dl) > 40) $dl .= PHP_EOL . "     ";
        $dl .= $data["basepath"] . $sd;        
    }
    $out[] = $dl;
    $out[] = "-Nextcloud Folders (where Nextcloud syncs to)";
    $dl ="  ->";
    foreach($data["nextcloud"]["accounts"] as $sd){
//        $out[] = "  ->" . $data["nextcloud"]["basedir"] . $sd;
        if(strlen($dl) > 8) $dl .= " / ";
//        if(strlen($dl) > 40) $dl .= PHP_EOL . "     ";
        $dl .= $data["nextcloud"]["basedir"] . $sd;
    }
    $out[] = $dl;
    $out[] = "";
    $out[] = "Syncing";
    $out[] = "-Nextcloud";
    $out[] = "    Nextcloud syncing is copying all new and yet unknown files to ";
    $out[] = "    " .  $data["nextcloud"]["syncdir"] ;
    $out[] = "    Afterwards phockup moves all new media files to  ";
    $out[] = "    /share/Datumsortierte_Bilder into the year-month-date folder";
    $out[] = "";
    $out[] = "Indexing";
    $out[] = '-Scan all files in $data["scandirs"]';
    $out[] = "    Scanning folders, adding not yet known files to fullindex, calculates filesizes and md5 checksums ";  
    $out[] = "-Scan datumssortierte";
    $out[] = "    Scans the folder /share/Datumsortierte_Bilder and updates the table datumssortierte with new files";    
    $out[] = "--";
    $out[] = "--";
    $out[] = "--";

    echo implode(PHP_EOL,$out);
}



function nextcloud_sync_cli(){
    echo "Sync nextcloud cli" . PHP_EOL;
    SyncNextCloud();
    echo PHP_EOL . "done" . PHP_EOL;
}

function scan_datumsortierte_cli(){
    echo "Scan datumsoertierte" . PHP_EOL;
    ScanDatumsSortierte();
    echo PHP_EOL . "done" . PHP_EOL;
}

function phockup_sync_autosort_cli(){
    echo "phockup_sync_autosort". PHP_EOL;
    phockupSortAUTOSORT();
    echo PHP_EOL . "done" . PHP_EOL;
}

function index_share_files_cli(){
    echo "Index shared files". PHP_EOL;

    IndexShareFiles();
    echo PHP_EOL . "done" . PHP_EOL;
}  

function phockup_sync_nextcloud_cli(){
    global $data;
    echo "Phockup new newcloud files" . PHP_EOL;
    phockupSort($data["nextcloud"]["syncdir"]);
    echo PHP_EOL . "done" . PHP_EOL;
}

function nextcloud_automatic_cli(){
    global $data;
    echo "Nextcloud auto-work" . PHP_EOL;
    echo "Step 1: Sync Nextcloud to AUTOSORT" . PHP_EOL;
    SyncNextCloud();
    echo "Step 2: Phockup files to datumsortierte" . PHP_EOL;
    phockupSort($data["nextcloud"]["syncdir"]);
    echo "Step 3: Scanning " . PHP_EOL . "-". implode( PHP_EOL . "-"  , $data["scandirs"] )  . PHP_EOL;
    IndexShareFiles();
    ScanDatumsSortierte();
    echo PHP_EOL . "--done--" . PHP_EOL;

}

/**
 * mycopy - copies a file creating the target path if not existing
 * @param mixed $s1 
 * @param mixed $s2 
 * @return void 
 */
function mycopy($s1, $s2) {
    $path = pathinfo($s2);
    if (!file_exists($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }
    if (!copy($s1, $s2)) {
        echo "copy failed \n";
    }
}
/**
 * GetFolderSize
 * @param mixed $folder 
 * @return string
 * returns the size of a folder in byte 
 */
function GetFolderSize($folder){
    $folder = trim($folder);
    $foldersize =  `du -scb "$folder" 2>/dev/null | head -n 1 | cut -f 1`;
    return $foldersize;
}
/**
 * GetFileNumber
 * @param mixed $folder 
 * @return string 
 * returns the number of files in a directory
 */

function GetFileNumber($folder){
    $folder = trim($folder);
    $filenum = `find "$folder" -maxdepth 1 -type f | wc -l`;
    return $filenum;
}


