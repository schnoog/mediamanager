<?php  


/**
 * Returns an array of md5 hashes of all files in fullindex
 * @return array 
 */
function GetIndexfilesMD5(){
	$md5in = array();
	$allav = DB::query("Select DISTINCT filemd5 from fullindex");
	for($x=0; $x < count($allav); $x++){
		$md5in[] = $allav[$x]["filemd5"];
	}
	return $md5in;
}


/**
 * Iterates through fullindex entries without md5 checksum OR filesize of 0
 * updates both, if real filesize is 0, db entry filesize is set to =1
 * @return void 
 */
function CompleteShareFilesIndex(){
    $all = DB::query("SELECT * FROM fullindex WHERE filemd5 = '' OR filesize = 0 ");
    for($x=0;$x < count($all);$x++){
        $line = $all[$x];
        $file = $line["filepath"];
        $id = $line["id"];
        $md5 = md5_file($file);
        $size = filesize($file);        
        DB::query("UPDATE fullindex SET filemd5 = %s , filesize = %i WHERE id = %i", $md5,$size,$id );
    }
    DB::query("UPDATE fullindex SET filesize = -1 WHERE filemd5 LIKE 'd41d8cd98f00b204e9800998ecf8427e' AND filesize = 0 ");
    // push empty files to size -1
}




/**
 * Iterates though $data["scandirs"] and calls file listing
 * @return void 
 */
function IndexShareFiles(){
    global $data;
    $newfiles = array();
    foreach($data["scandirs"] as $scansubdir){
        $scandir = $data["basepath"] . $scansubdir;
        IndexShareFolder($scandir, $newfiles);
    }
    CompleteShareFilesIndex();
}


/**
 * Lists all flies in $scandir, if file is not already in fullindex, it will be added (but no md5 or size calculated)
 * @param mixed $scandir 
 * @param array $newfiles 
 * @return array 
 */
function IndexShareFolder($scandir, &$newfiles = array()){
    global $data;

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator( $scandir ));
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
    
    
    $scf = DB::query("select filepath from fullindex");
    for($x = 0; $x < count($scf);$x++){
            $scanned[$scf[$x]["filepath"]] = 1;
    }
    

    $AddData = array();
    for($x = 0; $x < count($files);$x++){
        if(! isset( $scanned[ $files[$x]  ]  )) {
            $AddData[] = [
                "filepath" => $files[$x],
                "filename" => basename($files[$x]),
                "filemd5" => "",
                "filesize" => 0
            ];
            $newfiles[] = $files[$x];
            $new++;
        }else{
            $old++;
        }
    
        if( count($AddData) > 100) {
            DB::insert("fullindex",$AddData);
            $AddData = array();
        }
    }
    if( count($AddData) > 0) DB::insert("fullindex",$AddData);
    
    echo "$new New files added, $old skipped" . PHP_EOL;
    return $newfiles;
}
    
