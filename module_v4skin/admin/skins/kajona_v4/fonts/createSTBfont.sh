#!/bin/bash
# STB 11/2015
#
# create STB font with font custom an rename files
#

rm .fontcustom-manifest.json
rm -rf stbfont

fontcustom compile --name=stbfont stbfont_svg

cd stbfont

mv *.eot stbfont.eot
mv *.svg stbfont.svg
mv *.ttf stbfont.ttf
mv *.woff stbfont.woff
