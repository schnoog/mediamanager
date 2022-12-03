<?php
include_once("inc/loader.php");


ScanDatumsSortierte(false);



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



