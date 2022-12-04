<?php 



/**
 * 
 */
function phockupSortAUTOSORT(){
    global $data;
    $sortdir = $data["phockup_syncdir"];
    $phockupbin = $data["bashscript_folder"] . "SortPhotosMove.sh";
    $tmp = `$phockupbin "$sortdir"`; 
}

/**
 * 
 */
function phockupSort($sortdir){
    global $data;
    $phockupbin = $data["bashscript_folder"] . "SortPhotosMove.sh";
    $tmp = `$phockupbin "$sortdir"`; 
    //  echo $phockupbin .  " $sortdir" . PHP_EOL;

}

/**
 * 
 */
function GetImageInfo($imagepath){
    $imagepath = rtrim($imagepath);
    $retarray = array();
    $result = `/usr/bin/photils -i "$imagepath" -c | grep '1\.\|0\.99999'`;
    $retval = explode("\n",$result);
    $retval = array_filter($retval);
    for($x=0;$x < count($retval);$x++){
        list($tag,$val) = explode(":",$retval[$x]);
        $retarray[]  = $tag;
    }
    $retarray = array_filter($retarray);
     return $retarray;

}


/**
 * 
 * @param int $id 
 * @return void 
 */
function TagImages($id = 0){
    $tagdata = array();
    if($id == 0){
        $all = DB::query('SELECT * FROM `sharefiles` WHERE `sharefilepath` LIKE "/share/Datumsortierte_Bilder/%"');
    }else{
        $all = DB::query('SELECT * FROM `sharefiles` WHERE `id` = %i',$id);
    }
    for($x=0 ;$x < count($all);$x++){
        $fileid = $all[$x]["id"];
        $filepath = $all[$x]["sharefilepath"];
        echo $filepath . PHP_EOL;
        $res = GetImageInfo($filepath);
        for($y = 0; $y < count($res);$y++){
            $tagdata[] = [
                "fileid" => $fileid,
                "phototag" => $res[$y]
            ];
        }
        if(count($tagdata) > 0){
            DB::insert("phototags",$tagdata);
            $tagdata = array();
            echo "*";
        }

    }
    echo PHP_EOL;
}



/**
 * ScanDatumsSortierte -> scans /share/Datumsortierte_Bilder/ and adds filepath and md5 to table datumsortiert
 * @param bool $force_rescan -> Drops the content of datumsortiert and forces a rescan of all files
 * @return void 
 */
function ScanDatumsSortierte($force_rescan = false){
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( "/share/Datumsortierte_Bilder/" ));
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
    
    if(!$force_rescan){
        $scf = DB::query("select picfile from datumsortiert");
        for($x = 0; $x < count($scf);$x++){
            $scanned[$scf[$x]["picfile"]] = 1;
        }
    }else{
        DB::query("Delete from datumsortiert");
    }
    
    
    $AddData = array();
    for($x = 0; $x < count($files);$x++){
        if(! isset( $scanned[ $files[$x]  ]  )) {
            $AddData[] = [
                "picfile" => $files[$x],
                "picmd5" => md5_file($files[$x])
            ];
            $new++;
        }else{
            $old++;
        }
    
        if( count($AddData) > 100) {
            DB::insert("datumsortiert",$AddData);
            $AddData = array();
        }
    }
    if( count($AddData) > 0) DB::insert("datumsortiert",$AddData);
    
    echo "$new New files added, $old skipped" . PHP_EOL;
}
 
