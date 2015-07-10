#!/bin/bash

recipients='spatel@amperemedia.com';

/usr/bin/find /home/sites/admin.popularliving.com/html/admin/leads/ -depth -ctime +7 | mail -s "Lead Files to be Removed" $recipients

/usr/bin/find /home/sites/admin.popularliving.com/html/admin/leads/ -depth -ctime +7 | sed -e 's/\(.*\)/\"\1\"/' | /usr/bin/xargs rm -R -f

