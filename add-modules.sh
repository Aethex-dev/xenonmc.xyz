output=$(git pull)
echo "$output"
output=$(git submodule add https://github.com/XENONMC-DEV/XFRAME)
echo "$output"
output=$(git submodule add https://github.com/XENONMC-DEV/XFRAME)
git clone https://github.com/XENONMC-DEV/XFRAME
git submodule add https://github.com/XENONMC-DEV/XFRAME-ROUTER
git clone https://github.com/XENONMC-DEV/XFRAME-ROUTER
git submodule add https://github.com/XENONMC-DEV/tools
git clone https://github.com/XENONMC-DEV/tools
git submodule add https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder
git clone https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder
git submodule add https://github.com/XENONMC-DEV/XFORUM
git clone https://github.com/XENONMC-DEV/XFORUM
ls | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done
echo "Completed!"
