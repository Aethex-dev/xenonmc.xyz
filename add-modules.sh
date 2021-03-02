#!/bin/bash
git pull
ls | grep -Ev '.html|.md|.less|.sh|.jpg' | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done
echo "Completed!"
