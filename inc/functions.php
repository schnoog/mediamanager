<?php




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


