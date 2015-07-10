#!/bin/bash
 /home/scripts/pushPermissions.sh /home/sites/www.myhealthyliving.com/html/

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myhealthyliving.com/ root@w1.amperemedia.com:/home/sites/www.myhealthyliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myhealthyliving.com/ root@w2.amperemedia.com:/home/sites/www.myhealthyliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myhealthyliving.com/ root@w3.amperemedia.com:/home/sites/www.myhealthyliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myhealthyliving.com/ root@w4.amperemedia.com:/home/sites/www.myhealthyliving.com

##rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myhealthyliving.com/ root@w5.amperemedia.com:/home/sites/www.myhealthyliving.com

