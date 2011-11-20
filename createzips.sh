#!/bin/bash



init()
{
  echo "deleting target folder temp...";
  if [ -d "$PWD/packages" ]
  then
    rm -rf "$PWD/packages"
  fi
  mkdir "$PWD/packages"

  echo "creating exported folders...";
  for i in `ls -a "$PWD"`
  do
    if [ -d $i ] && [ $i != ".svn" ] && [ $i != "packages" ] && [ $i != "kajona" ] && [ $i != "." ] && [ $i != ".." ]
    then
      echo $i
      mkdir "$PWD/packages/$i"
      copyRecursive "$PWD/$i" "$PWD/packages/$i"
    fi
  done

}


singleZips()
{
  cd "packages"
  echo "single zips @ $PWD...";
  for i in `ls "$PWD"`
  do
    if [ -d "$PWD/$i" ]
    then
	zip -r "$i" "$i"
    fi
  done
  cd ".."
}

fullPackage()
{
  cd "packages"
  echo "full package...\n";
  mkdir "kajona";
  for i in `ls -a "$PWD"`
  do
    if [ -d "$PWD/$i" ] && [ $i != "kajona" ] && [ $i != "_debugging" ] && [ $i != "." ] && [ $i != ".." ]
    then
      echo $i
      copyRecursive "$PWD/$i" "$PWD/kajona/"
    fi
  done

  zip -r kajona-full.zip kajona;
  rm -rf "kajona";
  cd ".."
}


litePackage()
{
  cd "packages"
  echo "light package...\n";
  mkdir "kajona";
  for i in modul_navigation modul_pages modul_samplecontent module_system
  do
    if [ -d "$PWD/$i" ] && [ $i != "kajona" ] && [ $i != "_debugging" ]
    then
      echo $i
      copyRecursive "$PWD/$i" "$PWD/kajona/"
    fi
  done

  zip -r kajona-lite.zip kajona
  rm -rf "kajona";
  cd ".."
}

copyRecursive()
{ #startDir = 1, targetDir = 2
  for i in `ls -a "$1"`
  do
    if [ -d "$1/$i" ] && [ $i != ".svn" ] && [ $i != "." ] && [ $i != ".." ]
    then
      if [ ! -d "$2/$i" ]
      then
	mkdir "$2/$i"
      fi
      copyRecursive "$1/$i" "$2/$i"
    fi

    if [ -f "$1/$i" ]
    then
      cp "$1/$i" "$2/$i"
    fi

  done
}

echo "Packaging...\n";
init
singleZips
litePackage
fullPackage


