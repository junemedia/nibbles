#!/bin/bash

/home/scripts/pushPermissions.sh /home/sites/edu_amperemedia_com/html/

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/edu_amperemedia_com/ root@w1.amperemedia.com:/home/sites/edu_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/edu_amperemedia_com/ root@w2.amperemedia.com:/home/sites/edu_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/edu_amperemedia_com/ root@w3.amperemedia.com:/home/sites/edu_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/edu_amperemedia_com/ root@w4.amperemedia.com:/home/sites/edu_amperemedia_com

###rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/edu_amperemedia_com/ root@w5.amperemedia.com:/home/sites/edu_amperemedia_com

