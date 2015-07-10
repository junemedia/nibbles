
chmod -R 655 /home/sites/www_amperemedia_com/html/images
chmod 755 /home/sites/www_amperemedia_com/html/images
chmod 755 /home/sites/www_amperemedia_com/html/*.php
chmod 655 /home/sites/www_amperemedia_com/html/*.jpg
chmod 655 /home/sites/www_amperemedia_com/html/*.png
chmod 655 /home/sites/www_amperemedia_com/html/*.gif
chmod 655 /home/sites/www_amperemedia_com/html/*.htm
chmod 655 /home/sites/www_amperemedia_com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/ root@w1.amperemedia.com:/home/sites/www_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/ root@w2.amperemedia.com:/home/sites/www_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/ root@w3.amperemedia.com:/home/sites/www_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/ root@w4.amperemedia.com:/home/sites/www_amperemedia_com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www_amperemedia_com/ root@w5.amperemedia.com:/home/sites/www_amperemedia_com

