### push /home/sites/www_popularliving/html/images to .61, .75, .80, .110
### push /home/sites/www_popularliving/html/p to .61, .75, .80, 110
### push /home/sites/www_popularliving/html/bannerFarm to .61, .75, .80, .110
### push /home/sites/funpages.myfree.com/html/sounds and images to .61, .75, .80, .110

### A trailing / on a source name  means  "copy  the  contents  of this directory".  Without a trailing slash it means "copy the directory".

sScriptName="billsPush.sh";
source /home/scripts/includes/cssLogFunctionStart.sh

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/libs/pd_test.php root@64.132.70.75:/home/sites/www_popularliving_com/html/libs/pd_test.php

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/libs/pd_test.php root@64.132.70.80:/home/sites/www_popularliving_com/html/libs/pd_test.php

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/libs/pd_test.php root@64.132.70.61:/home/sites/www_popularliving_com/html/libs/pd_test.php

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/libs/pd_test.php root@64.132.70.110:/home/sites/www_popularliving_com/html/libs/pd_test.php


#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/banned_checker.php root@64.132.70.75:/home/sites/www_amperemedia_com/html/banned_checker.php

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/banned_checker.php root@64.132.70.80:/home/sites/www_amperemedia_com/html/banned_checker.php

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/banned_checker.php root@64.132.70.61:/home/sites/www_amperemedia_com/html/banned_checker.php

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/banned_checker.php root@64.132.70.110:/home/sites/www_amperemedia_com/html/banned_checker.php


#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/ajax.js root@64.132.70.75:/home/sites/www_amperemedia_com/html/ajax.js

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/ajax.js root@64.132.70.80:/home/sites/www_amperemedia_com/html/ajax.js

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/ajax.js root@64.132.70.61:/home/sites/www_amperemedia_com/html/ajax.js

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/html/ajax.js root@64.132.70.110:/home/sites/www_amperemedia_com/html/ajax.js

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/p/ root@64.132.70.75:/home/sites/www_popularliving_com/html/p

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/p/ root@64.132.70.80:/home/sites/www_popularliving_com/html/p

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/p/ root@64.132.70.61:/home/sites/www_popularliving_com/html/p

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/p/ root@64.132.70.110:/home/sites/www_popularliving_com/html/p



#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/bannerFarm/ root@64.132.70.75:/home/sites/www_popularliving_com/html/bannerFarm

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/bannerFarm/ root@64.132.70.80:/home/sites/www_popularliving_com/html/bannerFarm

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/bannerFarm/ root@64.132.70.61:/home/sites/www_popularliving_com/html/bannerFarm

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_popularliving_com/html/bannerFarm/ root@64.132.70.110:/home/sites/www_popularliving_com/html/bannerFarm




#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.80:/home/sites/funpages_myfree_com/html/images

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.80:/home/sites/funpages_myfree_com/html/sounds

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.75:/home/sites/funpages_myfree_com/html/images

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.75:/home/sites/funpages_myfree_com/html/sounds

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.61:/home/sites/funpages_myfree_com/html/images

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.61:/home/sites/funpages_myfree_com/html/sounds

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.110:/home/sites/funpages_myfree_com/html/images

#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.110:/home/sites/funpages_myfree_com/html/sounds


source /home/scripts/includes/cssLogFunctionFinish.sh
