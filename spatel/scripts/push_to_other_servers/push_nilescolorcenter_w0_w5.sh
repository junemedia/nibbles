chmod -R 655 /home/sites/www.nilescolorcenter.com/html/images
chmod 755 /home/sites/www.nilescolorcenter.com/html/images
chmod 755 /home/sites/www.nilescolorcenter.com/html/*.php
chmod 655 /home/sites/www.nilescolorcenter.com/html/*.jpg
chmod 655 /home/sites/www.nilescolorcenter.com/html/*.png
chmod 655 /home/sites/www.nilescolorcenter.com/html/*.gif
chmod 655 /home/sites/www.nilescolorcenter.com/html/*.htm
chmod 655 /home/sites/www.nilescolorcenter.com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.nilescolorcenter.com/ root@w1.amperemedia.com:/home/sites/www.nilescolorcenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.nilescolorcenter.com/ root@w2.amperemedia.com:/home/sites/www.nilescolorcenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.nilescolorcenter.com/ root@w3.amperemedia.com:/home/sites/www.nilescolorcenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.nilescolorcenter.com/ root@w4.amperemedia.com:/home/sites/www.nilescolorcenter.com

##rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.nilescolorcenter.com/ root@w5.amperemedia.com:/home/sites/www.nilescolorcenter.com

