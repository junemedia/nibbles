chmod -R 655 /home/sites/www.silverinet.com/html/images
chmod -R 655 /home/sites/www.silverinet.com/html/*.php
chmod 755 /home/sites/www.silverinet.com/html/images
chmod 655 /home/sites/www.silverinet.com/html/*.jpg
chmod 655 /home/sites/www.silverinet.com/html/*.png
chmod 655 /home/sites/www.silverinet.com/html/*.gif
chmod 655 /home/sites/www.silverinet.com/html/*.htm
chmod 655 /home/sites/www.silverinet.com/html/*.html

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.silverinet.com/ root@w1.amperemedia.com:/home/sites/www.silverinet.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.silverinet.com/ root@w2.amperemedia.com:/home/sites/www.silverinet.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.silverinet.com/ root@w3.amperemedia.com:/home/sites/www.silverinet.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.silverinet.com/ root@w4.amperemedia.com:/home/sites/www.silverinet.com

rsync -a --verbose  --progress --stats --compress --recursive --times --perms --links  --delete -e ssh /home/sites/www.silverinet.com/ root@w5.amperemedia.com:/home/sites/www.silverinet.com

