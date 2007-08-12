#!/bin/bash
#
#	sir, 2006
#	small script counting number of files an evaluates lines of code
#


regularFile() 
{ #return 0: true return 1: falsch
	# $1 = filename
	testpattern=".gif .png .jpg .sh~ .php~ .ttf .csv .sql .gz"
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
		if [ $i != "stats.sh" ] && [ $i != "copy.log" ] && [ $i != "copy.php" ] && [ $i != "local_backup.sh" ]
		then
			if [ -d "$1$i" ] && [ "$i" != ".svn" ]
			then
				checkLoc "$1$i/"
			fi
			if [ -f "$1$i" ] && regularFile "$i"
			then
				temp=$(cat "$1$i" | wc -l)
				nrLOC=$(($nrLOC + $temp))
			fi
		fi
	done
}


checkNrFiles() 
{		
	for i in `ls "$1"`
	do
		#echo $i
		if [ $i != "stats.sh" ] && [ $i != "copy.log" ] && [ $i != "copy.php" ] && [ $i != "local_backup.sh" ]
		then
			if [ -d "$1$i" ] && [ "$i" != ".svn" ]
			then
				checkNrFiles "$1$i/"
			fi
			if [ -f "$1$i" ]
			then
				nrFiles=$(($nrFiles + 1))
			fi
		fi
	done
}



echo "Analyzing files...."

nrFiles=0
nrLOC=0
echo "... counting files ..."
checkNrFiles "$PWD/"
echo "... counting LOC ..."
checkLoc "$PWD/"
echo "... finished"
echo ""
echo ""
echo "Number of files : $nrFiles"
echo "Lines of code   : $nrLOC"
