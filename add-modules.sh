#!/bin/bash
git pull
ls | grep -Ev '.html|.md|.less|.sh|.jpg|.png|.webp|.xml|.txt|.js' | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done
echo "Completed!"
