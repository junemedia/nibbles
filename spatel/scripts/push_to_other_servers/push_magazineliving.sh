chmod -R 655 /home/sites/www.magazineliving.com/html/images
chmod 755 /home/sites/www.magazineliving.com/html/images
chmod 755 /home/sites/www.magazineliving.com/html/*.php
chmod 655 /home/sites/www.magazineliving.com/html/*.jpg
chmod 655 /home/sites/www.magazineliving.com/html/*.png
chmod 655 /home/sites/www.magazineliving.com/html/*.gif
chmod 655 /home/sites/www.magazineliving.com/html/*.htm
chmod 655 /home/sites/www.magazineliving.com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.magazineliving.com/ root@w1.amperemedia.com:/home/sites/www.magazineliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.magazineliving.com/ root@w2.amperemedia.com:/home/sites/www.magazineliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.magazineliving.com/ root@w3.amperemedia.com:/home/sites/www.magazineliving.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.magazineliving.com/ root@w4.amperemedia.com:/home/sites/www.magazineliving.com

##rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.magazineliving.com/ root@w5.amperemedia.com:/home/sites/www.magazineliving.com

