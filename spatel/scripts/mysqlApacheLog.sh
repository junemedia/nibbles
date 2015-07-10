#!/bin/bash

#
# This script takes a log message from Apache 2 as STDIN, and logs it on s0's apache_log table
#

#cat - | echo "INSERT INTO sys_admin.apache_log (body, host) VALUES ('`cat -`','$HOSTNAME');" | mysql -hs0.amperemedia.com -uroot -p4r72K3dWe -Dsys_admin


