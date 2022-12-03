#!/usr/bin/bash




#
#	Sortiert die Bilder in den SOURCES Verzeichnissen in /share/Datumsortierte_Bilder/
#


FULLPATH=$(realpath "$0")
BASEDIR=$(dirname "$FULLPATH")



IFS="
"

SOURCES='/share/nextcloud/nadine/
/share/nextcloud/volker/
/share/Bilder/'


PHOTOSORTBIN="$BASEDIR""/bash_functions/SortPhotos.sh"


for SRC in $SOURCES
do

echo "$PHOTOSORTBIN" "$SRC"
"$PHOTOSORTBIN" "$SRC"

done