output = $(git pull)
echo $output
output = $(git submodule add https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1)
echo $output
output = $(git submodule add https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1)
git clone https://github.com/XENONMC-DEV/XFRAME >/dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFRAME-ROUTER >/dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/tools >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/tools >/dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/PHP-MySQLi-Query-Builder >/dev/null 2>&1
git submodule add https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1
git clone https://github.com/XENONMC-DEV/XFORUM >/dev/null 2>&1
ls | while read -r output; do cd $output && git pull | grep -v "Not a directory" && cd ..; done
echo "Completed!"
