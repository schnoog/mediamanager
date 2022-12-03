#!/usr/bin/bash



PID=$(ps aux | grep "php index.php" | grep -v grep | awk '{print $2}')
ls -l /proc/"$PID"/fd | grep "/share" | grep -v "/share/MediaManager/index.php"