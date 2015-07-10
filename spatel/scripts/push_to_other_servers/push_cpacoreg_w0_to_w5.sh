chmod -R 655 /home/sites/www.cpacoreg.com/html/images
chmod 755 /home/sites/www.cpacoreg.com/html/images
chmod 755 /home/sites/www.cpacoreg.com/html/*.php
chmod 655 /home/sites/www.cpacoreg.com/html/*.jpg
chmod 655 /home/sites/www.cpacoreg.com/html/*.png
chmod 655 /home/sites/www.cpacoreg.com/html/*.gif
chmod 655 /home/sites/www.cpacoreg.com/html/*.htm
chmod 655 /home/sites/www.cpacoreg.com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.cpacoreg.com/ root@w1.amperemedia.com:/home/sites/www.cpacoreg.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.cpacoreg.com/ root@w2.amperemedia.com:/home/sites/www.cpacoreg.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.cpacoreg.com/ root@w3.amperemedia.com:/home/sites/www.cpacoreg.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.cpacoreg.com/ root@w4.amperemedia.com:/home/sites/www.cpacoreg.com

###rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.cpacoreg.com/ root@w5.amperemedia.com:/home/sites/www.cpacoreg.com

