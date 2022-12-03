<?php 



/**
 * Iterates through nextcloud accounts syncfolders, searches for new files an copies them to $data["nextcloud"]["syncdir"]
 * Afterwards the phockup sync (in move mode) will be started
 * @return void 
 */
function SyncNextCloud(){
    global $data;
    $newfiles = array();
    foreach( $data["nextcloud"]["accounts"] as $account){
        ScanNextCloud($account,$newfiles);
    }

    for($x =0 ; $x < count($newfiles);$x++){
            $fn = $newfiles[$x];
            $nfn = str_replace($data["nextcloud"]["basedir"],$data["nextcloud"]["syncdir"],$fn);
            echo "Copy $fn to $nfn " . PHP_EOL;
            mycopy($fn,$nfn);
    }

    phockupSort($data["nextcloud"]["syncdir"]);
}


/**
 * Scans a nextcloud account folder for all files, if not in table nextcloud_scanned file will be added 
 * (and full path returned as array element to copy it)
 * 
 * @param string $account 
 * @param array $newfiles  By REF array which holds the new files not yet in nextcloud_scanned
 * @return array 
 */
function ScanNextCloud($account,&$newfiles = array()){
    global $data;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( $data["nextcloud"]["basedir"] . $account ));
    $files = array();
     
    $new = 0;
    $old = 0;
    foreach ($rii as $file) {
        if ($file->isDir()){ 
            continue;
        }
        $tmp = $file->getPathname(); 
        $files[] = $tmp;
    }
    $scanned = array();
    
    
    $scf = DB::query("select scannedfile from nextcloud_scanned");
    for($x = 0; $x < count($scf);$x++){
            $scanned[$scf[$x]["scannedfile"]] = 1;
    }
    
    $AddData = array();
    for($x = 0; $x < count($files);$x++){
        if(! isset( $scanned[ $files[$x]  ]  )) {
            $AddData[] = [
                "scannedfile" => $files[$x]
            ];
            $newfiles[] = $files[$x];
            $new++;
        }else{
            $old++;
        }
    
        if( count($AddData) > 100) {
            DB::insert("nextcloud_scanned",$AddData);
            $AddData = array();
        }
    }
    if( count($AddData) > 0) DB::insert("nextcloud_scanned",$AddData);
    
    echo "$new New files added, $old skipped" . PHP_EOL;
    return $newfiles;
}
    
