<?php 

function CheckNewFilesFromTable(){
	$available = GetSharefilesMD5();
	$isinid = array();
	$tocheck = DB::query("Select * from checkfiles WHERE ischecked = 0");
	for($x = 0; $x < count($tocheck); $x++){
		$id = $tocheck[$x]["id"];
		$md5 = $tocheck[$x]["checkmd5"];
		if( in_array( $md5, $available ) ){
			$isinid[] = $id;
		}

	}
	DB::query("UPDATE checkfiles SET isin = 1 WHERE id IN %li", $isinid);
}


function GetSharefilesMD5(){
	$md5in = array();
	$allav = DB::query("Select DISTINCT sharefilemd5 from sharefiles");
	for($x=0; $x < count($allav); $x++){
		$md5in[] = $allav[$x]["sharefilemd5"];
	}
	return $md5in;
}


/**
 * Adds File MD5 List to the table checkfiles 
 * @param mixed $indexfile 
 * @return false|void 
 */
function AddNewFileMD5($indexfile){
    global $data;
    $cnt=0;
    $AddData = array();
    if(! file_exists($indexfile)) return false;
    $handle = fopen($indexfile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            list($filemd5,$filesize,$filepath) = explode(" ",$line,3);
            $AddData[] = [
               
                "checkfile" => $filepath,
                "checkmd5" => $filemd5
            ];
            echo "*";
            $cnt++;
            if ($cnt == 99){
                echo PHP_EOL;
                $cnt = 0;
            }
        }
        echo PHP_EOL;
        $sql = "Delete from checkfiles ";
        DB::query($sql);
        DB::insert("checkfiles",$AddData);
        fclose($handle);
    }
}


/**
 * Checks file-md5-list against database and created a file with the move commands
 * @param mixed $filemd5list 
 * @return false|void 
 */
function CheckToSort($filemd5list){
    $cfln = str_replace("/","_",$filemd5list);
    $cfln = str_replace(".txt","",$cfln);
    global $data;
    $IsIn = array();
    $newfile[] = "#!/usr/bin/bash";
    $aqry = DB::query("SELECT DISTINCT sharefilemd5 from sharefiles");
    for($x = 0; $x < count($aqry);$x++){
        $md5 = $aqry[$x]['sharefilemd5'];
        $IsIn[$md5] = 1;

    }
    $cnt=0;
    $AddData = array();
    $data["workpath"] = $data["basepath"] . $workdir;
    $indexfile = $filemd5list ;
    if(! file_exists($indexfile)) return false;
    $handle = fopen($indexfile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            list($filemd5,$filesize,$filepath) = explode(" ",$line,3);
            $filepath = trim($filepath);
            if (isset( $IsIn[$filemd5] )){
                //file here
            }else{
                //file not here
                $newfile[] = "mv " . '"' . $filepath . '"' . " /share/Bilder/FromSort/ 2>/dev/null";
            }

        }
        fclose($handle);
    }
    file_put_contents('/dev/shm/' . $cfln .'_tocopy.txt', implode( "\n", $newfile )  );
}



/**
 * AddFolderMD5
 * @param mixed $workdir 
 * @return false|void
 * uses the share_folder_<workdir> file for initial data push (or complete refresh) to populate the table folders with folders, size and number of files
 */
function AddFolderMD5($workdir){
    global $data;
    $cnt=0;
    $AddData = array();
    $data["workpath"] = $data["basepath"] . $workdir;
    $indexfile = $data["indexdir"] . "share_folder_" . $workdir. ".txt" ;
    if(! file_exists($indexfile)) return false;
    $handle = fopen($indexfile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            list($foldersize,$folderpath) = explode(" ",$line,2);
            $folderpath = str_replace("\n",'',$folderpath);
            $filenumber = `find "$folderpath" -type f | wc -l `;
            $AddData[] = [
                "folder" => $folderpath,
                "foldermd5" => md5($folderpath),
                "foldersize" => $foldersize,
                "filenumber" => $filenumber,
                "lastchange" => time()
            ];
            echo "*";
            $cnt++;
            if ($cnt == 99){
                echo PHP_EOL;
                $cnt = 0;
            }
        }
        echo PHP_EOL;
        $sql = "Delete from folders WHERE folder LIKE %ss";
        DB::query($sql,$data["workpath"] . "/");
        $sql = "Delete from folders WHERE folder LIKE %s";
        DB::query($sql,$data["workpath"]);        
        DB::insert("folders",$AddData);
        fclose($handle);
    }
}

/**
 * AddFileMD5
 * @param mixed $workdir 
 * @return false|void 
 * uses the share_inde_<workdir> file for initial data push (or complete refresh) to populate the table sharefiles with files, size and md5 of files
 */

function AddFileMD5($workdir){
    global $data;
    $cnt=0;
    $AddData = array();
    $data["workpath"] = $data["basepath"] . $workdir;
    $indexfile = $data["indexdir"] . "share_index_" . $workdir. ".txt" ;
    if(! file_exists($indexfile)) return false;
    $handle = fopen($indexfile, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            list($filemd5,$filesize,$filepath) = explode(" ",$line,3);
            $AddData[] = [
                "sharefilename" => basename($filepath),
                "sharefilepath" => $filepath,
                "sharepathmd5" => md5($filepath),
                "sharefilemd5" => $filemd5,
                "sharefilesize" => $filesize
            ];
            echo "*";
            $cnt++;
            if ($cnt == 99){
                echo PHP_EOL;
                $cnt = 0;
            }
        }
        echo PHP_EOL;
        $sql = "Delete from sharefiles WHERE sharefilepath LIKE %ss";
        DB::query($sql,$data["workpath"]);
        DB::insert("sharefiles",$AddData);
        fclose($handle);
    }
}



/**
 * UpdateAllFolderFilenum  go through folders table and update no of files
 * @return void 
 */

function UpdateAllFolderFilenum(){
    global $data;
    $Upd = array();
    $folders = DB::query("Select id, folder from folders");
    for($x=0;$x < count($folders);$x++){
        $fld = $folders[$x];
        $id = $fld['id'];
        $folder = $fld['folder'];
        $filenum = GetFileNumber($folder);

        DB::query("Update folders SET filenumber = %i WHERE id = %i",$filenum,$id);
        echo DB::lastQuery();

    }
}

/**
 * 
 * CheckFolder
 * @param mixed $workdir  (sub-directory of the main data directory , f.e. Music -> /share/<Music>)
 * @return void 
 * checks if the overall size of a folder has changed, if so, the folder is added to table fcheck
 */
function CheckFolder($workdir){
    global $data;
    $data["debug"][] =  "Checking Folder $workdir";
    $data["workpath"] = $data["basepath"] . $workdir;
    $tmp = $data["workpath"];
        $checkdat = DB::query("Select folder, foldersize from folders WHERE folder LIKE %ss",$tmp);
    for($x = 0; $x < count($checkdat);$x++){
        $checkdata[ $checkdat[$x]["folder"] ] = $checkdat[$x]["foldersize"];
    }
    $sql = "Delete from fcheck WHERE folder LIKE %ss";
    DB::query($sql,$data["workpath"] . "/");
    $sql = "Delete from fcheck WHERE folder LIKE %s";
    DB::query($sql,$data["workpath"]);   
    $allfolderstring = `find '$tmp' -maxdepth 1 -type d`;
    $allfolders = explode("\n",$allfolderstring);
    $allfolders = array_filter($allfolders);
    $tocheck = array();
    foreach($allfolders as $folder){
        $foldersize = GetFolderSize($folder);
        $data["debug"][] = "-Checksub $folder ";
        if(! isset($checkdata[$folder])) $checkdata[$folder] = "9999999999";
        if ($foldersize != $checkdata[$folder]) {
            $tocheck[] = [
                "folder" => $folder
            ];
        }
    }
    if(count($tocheck)>0) DB::insert("fcheck",$tocheck);
}


function CompleteShareFilesIndexWith(){
    $updatedata = array();
    $shareindex = array();
    $oldindex = DB::query("SELECT sharefilepath,sharefilemd5,sharefilesize FROM sharefiles");
    for($x=0;$x < count($oldindex);$x++){
        $line = $oldindex[$x];
        $shareindex[$line['sharefilepath'] ] = [
            "md5" => $line["sharefilemd5"],
            "size" => $line["sharefilesize"]
        ];
    }    

    $all = DB::query("SELECT * FROM fullindex WHERE filemd5 = '' OR filesize = 0 ");
    for($x=0;$x < count($all);$x++){
        $line = $all[$x];
        $file = $line["filepath"];
        $id = $line["id"];

        if (isset( $shareindex[$file]  )){
            $md5 = $shareindex[$file]["md5"];
            $size = $shareindex[$file]["size"];

        }else{
            $md5 = md5_file($file);
            $size = filesize($file);


        }
        
        DB::query("UPDATE fullindex SET filemd5 = %s , filesize = %i WHERE id = %i", $md5,$size,$id );

    }
    DB::query("UPDATE fullindex SET filesize = -1 WHERE filemd5 LIKE 'd41d8cd98f00b204e9800998ecf8427e' AND filesize = 0 ");
    // push empty files to size -1

}