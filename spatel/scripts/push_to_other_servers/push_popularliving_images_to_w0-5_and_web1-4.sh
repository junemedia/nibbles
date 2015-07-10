#!/bin/bash

## A trailing / on a source name  means  "copy  the  contents  of this directory".  Without a trailing slash it means "copy the directory".

#sScriptName="push.sh";
#source /home/scripts/includes/cssLogFunctionStart.sh

/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/images/
#/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/p/
/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/bannerFarm/
/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/nibbles2/


#w1..w5
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/images/ root@64.132.70.31:/home/sites/www_popularliving_com/html/images
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/images/ root@64.132.70.32:/home/sites/www_popularliving_com/html/images
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/images/ root@64.132.70.33:/home/sites/www_popularliving_com/html/images
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/images/ root@64.132.70.34:/home/sites/www_popularliving_com/html/images
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/images/ root@64.132.70.35:/home/sites/www_popularliving_com/html/images


#w1..w5
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/p/ root@64.132.70.31:/home/sites/www_popularliving_com/html/p
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/p/ root@64.132.70.32:/home/sites/www_popularliving_com/html/p
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/p/ root@64.132.70.33:/home/sites/www_popularliving_com/html/p
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/p/ root@64.132.70.34:/home/sites/www_popularliving_com/html/p
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/p/ root@64.132.70.35:/home/sites/www_popularliving_com/html/p


#w1..w5
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.31:/home/sites/www_popularliving_com/html/bannerFarm
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.32:/home/sites/www_popularliving_com/html/bannerFarm
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.33:/home/sites/www_popularliving_com/html/bannerFarm
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.34:/home/sites/www_popularliving_com/html/bannerFarm
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/admin.popularliving.com/html/bannerFarm/ root@64.132.70.35:/home/sites/www_popularliving_com/html/bannerFarm



#w1..w5
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/libs/jsPopFuncs.js root@64.132.70.31:/home/sites/www_popularliving_com/html/libs/
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/libs/jsPopFuncs.js root@64.132.70.32:/home/sites/www_popularliving_com/html/libs/
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/libs/jsPopFuncs.js root@64.132.70.33:/home/sites/www_popularliving_com/html/libs/
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/libs/jsPopFuncs.js root@64.132.70.34:/home/sites/www_popularliving_com/html/libs/
#rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/libs/jsPopFuncs.js root@64.132.70.35:/home/sites/www_popularliving_com/html/libs/

#w1..w5
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/nibbles2/ root@64.132.70.31:/home/sites/www_popularliving_com/html/nibbles2
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/nibbles2/ root@64.132.70.32:/home/sites/www_popularliving_com/html/nibbles2
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/nibbles2/ root@64.132.70.33:/home/sites/www_popularliving_com/html/nibbles2
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/nibbles2/ root@64.132.70.34:/home/sites/www_popularliving_com/html/nibbles2
#rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/html/nibbles2/ root@64.132.70.35:/home/sites/www_popularliving_com/html/nibbles2



#source /home/scripts/includes/cssLogFunctionFinish.sh
