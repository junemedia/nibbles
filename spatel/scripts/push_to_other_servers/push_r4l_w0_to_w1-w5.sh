/home/scripts/pushR4LPermissions.sh /home/sites/www.recipe4living.com/

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.recipe4living.com/ root@w1.amperemedia.com:/home/sites/www.recipe4living.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.recipe4living.com/ root@w2.amperemedia.com:/home/sites/www.recipe4living.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.recipe4living.com/ root@w3.amperemedia.com:/home/sites/www.recipe4living.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.recipe4living.com/ root@w4.amperemedia.com:/home/sites/www.recipe4living.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.recipe4living.com/ root@w5.amperemedia.com:/home/sites/www.recipe4living.com

