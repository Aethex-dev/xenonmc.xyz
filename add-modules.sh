#!/bin/bash
git pull
ls | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done
echo "Completed!"
