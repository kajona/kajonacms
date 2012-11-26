#!/bin/bash



createModuleZips() 
{ 
  echo "deleting target folder packages...";
  if [ -d "$PWD/packages" ]
  then
    rm -rf "$PWD/packages"
  fi
  mkdir "$PWD/packages"

  echo "creating single zips folders...";
  
  for i in `ls -a "$PWD/temp/kajona/core"`
  do
    if [ -d "$PWD/temp/kajona/core/$i" ] && [ $i != ".svn" ] && [ $i != "packages" ] && [ $i != "kajona" ] && [ $i != "." ] && [ $i != ".." ]
    then
      echo $i
      cd "temp/kajona/core/$i/"
      zip -r -p "../../../../packages/$i" "."
      cd "../../../../" 

    fi
  done
  
}


echo "Packaging...\n";

echo "calling the ant task modulePackagesZipHelper\n";
ant -f $PWD/build_jenkins.xml modulePackagesZipHelper
createModuleZips

