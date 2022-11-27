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


UpdateAllFolderFilenum();






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