<?php 

declare(strict_types=1);

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;

include_once("inc/loader.php");

$itemCallable = function (CliMenu $menu) {
    echo $menu->getSelectedItem()->getText();
};






$data["verbose-copy"] = false;


$menu = (new CliMenuBuilder)
    ->setTitle('Media Manager CLI')
    ->enableAutoShortcuts()

    ->addSubMenu('[N]extCloud-Sync', function (CliMenuBuilder $a) {
        $a->setTitle('Media Manager CLI > NextCloud-Sync')
            ->addItem('[A]utomatic run', function (CliMenu $menu) {
                if(Confirm($menu,"Start Nextcloud Autosync?")) nextcloud_automatic_cli();
            })
            ->addLineBreak('-')        
            ->addItem('[1]Sync-Nextcloud with database and copy files to AUTOSORT', function (CliMenu $menu) {
                if(Confirm($menu,"Start extracting new nextcloud files?")) nextcloud_sync_cli();
            })
            ->addItem('[2]Phockup-Nextcloud copies in AUTOSORT to datumssortiert', function (CliMenu $menu) {
                if(Confirm($menu,"Start Phockup Nextcloud Sync?")) phockup_sync_nextcloud_cli();
            })            
            ->addItem('[3]Sync-Datumssortierte Bilder table entries', function (CliMenu $menu) {
                if(Confirm($menu,"Start refreshing Datumssortierte Bilder?")) scan_datumsortierte_cli();
            })
            ->addItem('[4]Scan all files in $data["scandirs"] into fullindex', function (CliMenu $menu) {
                if(Confirm($menu,"Start refreshing fullindex ?")) index_share_files_cli();
            })
            ->addLineBreak('-');
    })


    ->addSubMenu('[I]ndexing', function (CliMenuBuilder $c) {
        $c->setTitle('CLI Menu > Index')
            ->addItem('Scan [a]ll files in $data["scandirs"]', function (CliMenu $menu) {
                if(Confirm($menu,"Scan all files"))    index_share_files_cli();
            })
            ->addItem('Scan [d]atumssortierte', function (CliMenu $menu) {
                if(Confirm($menu,"Scan all images in Datumsortierte_Bilder")) scan_datumsortierte_cli();
            })            
            ->addLineBreak('-');
    })



    ->addLineBreak('-')
    ->addItem('Inf[o]', function (CliMenu $menu) {
        info_cli();
    }) 

/*
    ->addItem('First Item', $itemCallable)

->addSubMenu('Compiled', function (CliMenuBuilder $d) use ($itemCallable) {
    $d->setTitle('Compiled Languages')
        ->addRadioItem('Rust', $itemCallable)
        ->addRadioItem('Go', $itemCallable)
        ->addRadioItem('Java', $itemCallable)
        ->addRadioItems([
            ['C++', $itemCallable],
            ['C', $itemCallable]
        ])
    ;
})
*/
    ->setWidth(70)
    ->setBackgroundColour('black')
    ->build();

$menu->open();





$args = $argv[0];

echo $args . PHP_EOL;





function Confirm(&$menu, $msg = "Really doing that?"){
    //global $menu;
    $continue = $menu->cancellableConfirm($msg)
        ->display('Yes', 'No, abort');

    if ($continue) {
        // Something Destructive
        $ret = true;
    } else {
        // Do nothing
        $ret = false;
    }
    return $ret;
}