#!/bin/bash

##rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/validateAddress/ root@64.132.70.31:/home/sites/www_popularliving_com/validateAddress
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/validateAddress/ root@64.132.70.32:/home/sites/www_popularliving_com/validateAddress
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/validateAddress/ root@64.132.70.33:/home/sites/www_popularliving_com/validateAddress
rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/admin.popularliving.com/validateAddress/ root@64.132.70.34:/home/sites/www_popularliving_com/validateAddress


## use below rsync to push from test to admin on w0.  once approved, then run this script to push to live servers.
##rsync -a --verbose --progress --stats --compress --recursive --times --perms --links --delete -e ssh /home/sites/test.popularliving.com/validateAddress/ /home/sites/admin.popularliving.com/validateAddress
