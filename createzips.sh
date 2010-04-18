#!/bin/bash



echo "deleting zips\n"
rm *.zip;

echo "single zips...\n";
for i in `ls "$PWD"`
do
  if [ -d "$PWD/$i" ]
  then
      cd "$i";
      zip -r "../$i" *
      cd "..";
  fi
done

echo "full package...\n";
mkdir "kajona";
for i in `ls "$PWD"`
do
  if [ -d "$PWD/$i" ] && [ "$i" != "kajona" ] && [ "$i" != "_debugging" ]
  then
    cd $i;
    cp -Rf * "../kajona/";
    cd "..";
  fi
done

cd "kajona";
zip -r "../kajona-full.zip" *;
cd "..";
rm -rf "kajona";

echo "light package...\n";
mkdir "kajona";
for i in modul_navigation modul_pages modul_samplecontent modul_system
do
  if [ -d "$PWD/$i" ] && [ "$i" != "kajona" ] && [ "$i" != "_debugging" ]
  then
    echo $i;
    cd $i;
    cp -Rf * "../kajona/";
    cd "..";
  fi
done

cd "kajona";
zip -r "../kajona-lite.zip" *;
cd "..";
rm -rf "kajona";

