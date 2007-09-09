#!/bin/bash
#
#	sir, 2006
#	small script counting number of files an evaluates lines of code
#


regularFile() 
{ #return 0: true return 1: falsch
	# $1 = filename
	testpattern=".gif .png .jpg .sh~ .php~ .ttf .csv .sql .gz .log .ico .ini"
	for check in $testpattern
	do
		grepCheck=$(echo $i | grep $check)
		anzahlCount=$(echo $grepCheck | wc -m )
		#echo "pattern "$check" on file "$1" result: "$grepCheck" count "$anzahlCount
		if [ $anzahlCount != "1" ]
		then
			return 1
		fi
	done
	return 0
}


checkLoc()
{
	#echo "scanning $1..."
	for i in `ls "$1"`
	do
		#echo $i
		if [ $i != "stats.sh" ] && [ $i != "copy.log" ] && [ $i != "copy_down.log" ] && [ $i != "copy.php" ] && [ $i != "local_backup.sh" ]
		then
			if [ -d "$1$i" ] && [ "$i" != ".svn" ]
			then
				checkLoc "$1$i/"
				
			fi
			if [ -f "$1$i" ]
			then
				if regularFile "$i"
				then
					temp=$(cat "$1$i" | wc -l)
					nrLOC=$((nrLOC + $temp))
					nrLOCFiles=$((nrLOCFiles + 1))
					if checkKajonaLoc "$1$i"
					then
						nrKajonaLOC=$((nrKajonaLOC + $temp))
						nrKajonaLOCFiles=$((nrKajonaLOCFiles + 1))
					fi
				fi
				nrFilesAnalyzed=$((nrFilesAnalyzed + 1))
				echo -n -e "\r... analyzed $nrFilesAnalyzed of $nrFiles files ..."
			fi
		fi
	done
}

checkKajonaLoc()
{
	testpattern="yui fckeditor jscalendar system/fonts jpgraph ip2country"
	for check in $testpattern
	do
		grepCheck=$(echo $1 | grep $check)
		anzahlCount=$(echo $grepCheck | wc -m )
		if [ $anzahlCount != "1" ]
		then
			#echo "excluding " $1 "out of kajona LOC"
			return 1
		fi
	done
	#echo "kLOC: $1"
	return 0	
}

checkNrFiles() 
{		
	for i in `ls "$1"`
	do
		if [ $i != "stats.sh" ] && [ $i != "copy.log" ] && [ $i != "copy_down.log" ] && [ $i != "copy.php" ] && [ $i != "local_backup.sh" ]
		then
			if [ -d "$1$i" ] && [ "$i" != ".svn" ]
			then
				checkNrFiles "$1$i/"
			fi
			if [ -f "$1$i" ]
			then
				nrFiles=$((nrFiles + 1))
			fi
		fi
	done
}



echo "Analyzing files..."

nrFiles=0
nrFilesAnalyzed=0
nrLOC=0
nrKajonaLOC=0
nrLOCFiles=0
nrKajonaLOCFiles=0
echo "... counting files ..."
checkNrFiles "$PWD/"
echo "... counting LOC ..."
checkLoc "$PWD/"
echo ""
echo "... finished"
echo ""
echo ""
echo "Number of files       : $nrFiles"
echo ""
echo "Total lines of code   : $nrLOC"
echo "Files analyzed        : $nrLOCFiles"
echo ""
echo "Kajona lines of code  : $nrKajonaLOC"
echo "Files analyzed        : $nrKajonaLOCFiles"
echo ""
echo ""
