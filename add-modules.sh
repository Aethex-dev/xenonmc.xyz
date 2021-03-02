#!/bin/bash
git pull
echo $(ls | grep -Ev '.html|.md|.less|.sh|.jpg|.png|.webp|.xml|.txt|.js|.php|.jpeg' | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done)
echo "Completed!"
