mkdir test && cd test
git init
git submodule add https://github.com/Modfwango/Modfwango.git .modfwango
cp .modfwango/launcher.php main.php
php main.php prelaunch
touch conf/noupdatecheck
