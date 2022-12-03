#!/usr/bin/bash


SOURCE="$*"
TARGET='/share/Datumsortierte_Bilder/'

if [ "$SOURCE" == "" ]
then
echo "$(basename "$0")"" <source directory>"
echo "Sorts images from given folder into $TARGET"
exit 1
fi



LOG='--log=/dev/shm/phockup_log.txt'
REGEX='--regex="(?P<day>\d{2})\.(?P<month>\d{2})\.(?P<year>\d{4})[_-]?(?P<hour>\d{2})\.(?P<minute>\d{2})\.(?P<second>\d{2})"'




/usr/local/bin/phockup -m $REGEX $LOG "$SOURCE" "$TARGET"
cat /dev/shm/phockup_log.txt > /share/MediaManager/logs/phockup_log.txt
rm /dev/shm/phockup_log.txt

