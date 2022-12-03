#!/usr/bin/bash
IFS="
"


FFILES="/share/Sonstiges/01_Elektronik/Box_Development/ABC.txt
/share/Sonstiges/01_Elektronik/Arduino/Wire_Master_Test/ABC.txt
/share/Sonstiges/07_Finanzamt_und_behoerden/Steuerfälle/FA/ABC.txt
"


CREATE=1

for FILE in $FFILES
do

	if [ "$CREATE" == "1" ]
	then
		date > "$FILE"
	else
		rm "$FILE"
	fi


done