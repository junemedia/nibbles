### push /home/sites/www_popularliving/html/images to .61, .75, .80, .110
### push /home/sites/www_popularliving/html/p to .61, .75, .80, 110
### push /home/sites/www_popularliving/html/bannerFarm to .61, .75, .80, .110
### push /home/sites/funpages.myfree.com/html/sounds and images to .61, .75, .80, .110

### A trailing / on a source name  means  "copy  the  contents  of this directory".  Without a trailing slash it means "copy the directory".

#sScriptName="push.sh";
#source /home/scripts/includes/cssLogFunctionStart.sh

#chmod -R 755 /home/sites/admin.popularliving.com/html/images
#chmod -R 755 /home/sites/admin.popularliving.com/html/p
#chmod 755 /home/sites/admin.popularliving.com/html/images
#chmod 755 /home/sites/admin.popularliving.com/html/p
#chmod 655 /home/sites/admin.popularliving.com/html/*.jpg
#chmod 655 /home/sites/admin.popularliving.com/html/*.png
#chmod 655 /home/sites/admin.popularliving.com/html/*.gif
#chmod 655 /home/sites/admin.popularliving.com/html/*.htm
#chmod 655 /home/sites/admin.popularliving.com/html/*.html

/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/nibbles2/
/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/images/
/home/scripts/pushPermissions.sh /home/sites/admin.popularliving.com/html/bannerFarm/

#web1 .. web4
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.75:/home/sites/www_popularliving_com/html
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.80:/home/sites/www_popularliving_com/html
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.61:/home/sites/www_popularliving_com/html
#rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.125:/home/sites/www_popularliving_com/html

#w1..w5
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.31:/home/sites/www_popularliving_com/html
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.32:/home/sites/www_popularliving_com/html
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.33:/home/sites/www_popularliving_com/html
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.34:/home/sites/www_popularliving_com/html
rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links -e ssh /home/sites/admin.popularliving.com/html/ root@64.132.70.35:/home/sites/www_popularliving_com/html

