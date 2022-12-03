<?php


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


