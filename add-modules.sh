# E Lucas Burlingham Mon Feb 22 07:57:55 PM EST 2021
#!/bin/bash
git pull                                                                # Pull the latest version of the script for the next time the user runs it
git submodule add https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1 # redirect any errors to /dev/null
git clone https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1         # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/tools >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/tools >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1 # clone just incase there is an error
git submodule add https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1                                 # clone just incase there is an error
ls | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done # for each directory in the current directory, change into that directory and run `git pull`
echo "Completed!"
