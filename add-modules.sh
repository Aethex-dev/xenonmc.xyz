#!/bin/bash
# Set colors
Yellow='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'                                                            # No Color
git submodule add https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1 # redirect any errors to /dev/null
git clone https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1         # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/tools >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/tools >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1       # clone just incase there is an error
ls -d | while read -r output; do cd $output && git pull && cd -; done # for each directory in the current directory, change into that directory and run `git pull`
echo -e "${GREEN}Completed!${NC}"
