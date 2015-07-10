#!/bin/bash

#
#	Deletes the old local log files
#	Collects the rotated logs from a given server
#	Deletes the old remote log files
#	Concatenates the local log files
#

#rm /var/log/apache2/aggregate.log
mkdir /var/log/apache2/templogs

#scp root@$1:/var/log/apache2/$2.log* /var/log/apache2/templogs/
scp root@$1:/var/log/apache2/$2.log.* /var/log/apache2/templogs/

#delete remotely
#ssh root@$1 "/bin/rm /var/log/apache2/$2.log*"
ssh root@$1 "/bin/rm /var/log/apache2/$2.log.*"

touch /var/log/apache2/$2.log

#if there are any gzipped logs here, unzip them
for i in $( ls /var/log/apache2/templogs/ | grep 'gz$' | sort ); do
	gunzip /var/log/apache2/templogs/$i >> /var/log/apache2/templogs/`echo $i | tr -t '.gz' ''`
done

rm -r /var/log/apache2/templogs/*.gz

for i in $( ls /var/log/apache2/templogs/ | sort ); do
	cat /var/log/apache2/templogs/$i >> /var/log/apache2/$2.log
done

rm -r /var/log/apache2/templogs

