<?php



include_once("loader.php");







//$workdir = "Bilder";
//$workdir = "Sonstiges";
//$workdir = "Datumsortierte_Bilder";


//$data["scandirs"] = ["Bilder"];

foreach ($data["scandirs"] as $workdir){
//    AddFolderMD5($workdir);
//    AddFileMD5($workdir);
//    CheckFolder($workdir);
}




//UpdateAllFolderFilenum();

//$indexfile= "/share/MediaManager/share_index_new_cleaned.txt";

//AddNewFileMD5($indexfile);
//CheckNewFilesFromTable();

//$work = GetImageInfo("/share/Datumsortierte_Bilder/2019/02/16/20190216-114205626531.jpg");
//$work = array_filter($work);

$data["debug"] = ""; //$work;
//TagImages();

//CheckToSort("/share/MediaManager/lists/_share_Sortierte_Bilder_.txt");



/**
 * 
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





//$scandir = $data["basepath"]  . "Sonstiges/01_Elektronik/eagle/";

//$tmp = GetFolders($scandir);

//$tmp = BuildFolderArray($tmp);

//echo print_r($tmp,true);
//$x = "/share/Sonstiges/01_Elektronik/Arduino";
//$folderarray = array();
//$tmp = listFolders($x);
echo print_r($data["debug"],true);






/*


function SplitFolder($folder,&$folderarray){
    $subs = explode("/",$folder);
    foreach ($subs as $sub){
        
    }

}








*/