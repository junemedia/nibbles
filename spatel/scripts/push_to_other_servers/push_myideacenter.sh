chmod -R 655 /home/sites/www.myideacenter.com/html/images
chmod 755 /home/sites/www.myideacenter.com/html/images
chmod 755 /home/sites/www.myideacenter.com/html/*.php
chmod 655 /home/sites/www.myideacenter.com/html/*.jpg
chmod 655 /home/sites/www.myideacenter.com/html/*.png
chmod 655 /home/sites/www.myideacenter.com/html/*.gif
chmod 655 /home/sites/www.myideacenter.com/html/*.htm
chmod 655 /home/sites/www.myideacenter.com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myideacenter.com/ root@w1.amperemedia.com:/home/sites/www.myideacenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myideacenter.com/ root@w2.amperemedia.com:/home/sites/www.myideacenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myideacenter.com/ root@w3.amperemedia.com:/home/sites/www.myideacenter.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myideacenter.com/ root@w4.amperemedia.com:/home/sites/www.myideacenter.com

##rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.myideacenter.com/ root@w5.amperemedia.com:/home/sites/www.myideacenter.com

