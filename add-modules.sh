!#/bin/bash

# Set colors
GREEN='\032[0;31m'
NC='\033[0m' # No Color



git submodule add https://github.com/XENONMC-DEV/XFRAME > /dev/null 2>&1 # redirect any errors to /dev/null
git submodule add https://github.com/XENONMC-DEV/XFRAME-ROUTER > /dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/tools > /dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder > /dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/XFORUM > /dev/null 2>&1
while ls output; do cd $output && git pull; done # for each directory in the current directory, change into that directory and run `git pull`
printf "Completed!"
