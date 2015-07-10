#!/bin/bash

/home/scripts/pushPermissions.sh /home/sites/funpages_myfree_com/html/

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/ root@w1.amperemedia.com:/home/sites/funpages_myfree_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/ root@w2.amperemedia.com:/home/sites/funpages_myfree_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/ root@w3.amperemedia.com:/home/sites/funpages_myfree_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages_myfree_com/ root@w4.amperemedia.com:/home/sites/funpages_myfree_com

###rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/funpages.myfree.com/ root@w5.myfree.com:/home/sites/funpages.myfree.com

