#!/bin/bash

/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/editors/

/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/damin.popularliving.com/html/editors/ root@64.132.70.110:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/editors/ root@64.132.70.31:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/editors/ root@64.132.70.32:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/editors/ root@64.132.70.33:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/editors/ root@64.132.70.34:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/editors/ root@64.132.70.35:/home/sites/www_popularliving_com/html/editors 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.110:/home/sites/www_popularliving_com/html/bannerFarm 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.31:/home/sites/www_popularliving_com/html/bannerFarm 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.32:/home/sites/www_popularliving_com/html/bannerFarm 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.33:/home/sites/www_popularliving_com/html/bannerFarm 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.34:/home/sites/www_popularliving_com/html/bannerFarm 2>&1
/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.35:/home/sites/www_popularliving_com/html/bannerFarm 2>&1

#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.80:/home/sites/funpages_myfree_com/html/images 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.80:/home/sites/funpages_myfree_com/html/sounds 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.75:/home/sites/funpages_myfree_com/html/images 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.75:/home/sites/funpages_myfree_com/html/sounds 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.61:/home/sites/funpages_myfree_com/html/images 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.61:/home/sites/funpages_myfree_com/html/sounds 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/images/ root@64.132.70.110:/home/sites/funpages_myfree_com/html/images 2>&1
#/usr/bin/rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/html/sounds/ root@64.132.70.110:/home/sites/funpages_myfree_com/html/sounds 2>&1

