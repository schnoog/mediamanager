<?php 

declare(strict_types=1);

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;

include_once("inc/loader.php");

$itemCallable = function (CliMenu $menu) {
    echo $menu->getSelectedItem()->getText();
};






$menu = (new CliMenuBuilder)
    ->setTitle('CLI Menu')
    ->addItem('First Item', $itemCallable)
    ->addLineBreak('-')
    ->addSubMenu('Sync', function (CliMenuBuilder $b) {
        $b->setTitle('CLI Menu > Sync')
            ->addItem('Sync-Nextcloud with database and copy files', function (CliMenu $menu) {
                nextcloud_sync_cli();
            })
            ->addItem('Phockup-Nextcloud to datumssortiert', function (CliMenu $menu) {
                phockup_sync_nextcloud_cli();
            })            
            ->addItem('phockup - Autosort -move images', function (CliMenu $menu) {
                phockup_sync_autosort_cli();
            })
            ->addItem('Sync-Datumssortierte Bilder', function (CliMenu $menu) {
                scan_datumsortierte_cli();
            })
            ->addLineBreak('-');
    })
    ->addSubMenu('Index', function (CliMenuBuilder $c) {
        $c->setTitle('CLI Menu > Index')
            ->addItem('Scan all files in $data["scandirs"]', function (CliMenu $menu) {
                index_share_files_cli();
            })
            ->addLineBreak('-');
    })
    ->setWidth(70)
    ->setBackgroundColour('black')
    ->build();

$menu->open();





$args = $argv[0];

echo $args . PHP_EOL;


