#!/bin/bash

#
#	remove the old aggregate log
#	run scripts for each server
#


#bzip2 -c /var/log/apache2/aggregate.log >> /var/log/apache2/aggregate.log.$(date +%Y%m%d).bz2

#www.amperemedia.com
rm /var/log/apache2/www.amperemedia.com.log
touch /var/log/apache2/www.amperemedia.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.amperemedia.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.amperemedia.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.amperemedia.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.amperemedia.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.amperemedia.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.amperemedia.com -update

#www.popularliving.com
rm /var/log/apache2/www.popularliving.com.log
touch /var/log/apache2/www.popularliving.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.popularliving.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.popularliving.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.popularliving.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.popularliving.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.popularliving.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.popularliving.com -update

#www.recipe4living.com
rm /var/log/apache2/www.recipe4living.com.log
touch /var/log/apache2/www.recipe4living.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.recipe4living.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.recipe4living.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.recipe4living.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.recipe4living.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.recipe4living.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.recipe4living.com -update

#www.cpacoreg.com
rm /var/log/apache2/www.cpacoreg.com.log
touch /var/log/apache2/www.cpacoreg.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.cpacoreg.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.cpacoreg.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.cpacoreg.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.cpacoreg.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.cpacoreg.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.cpacoreg.com -update

#www.silverinet.com
rm /var/log/apache2/www.silverinet.com.log
touch /var/log/apache2/www.silverinet.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.silverinet.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.silverinet.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.silverinet.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.silverinet.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.silverinet.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.silverinet.com -update


#content.popularliving.com
rm /var/log/apache2/content.popularliving.com.log
touch /var/log/apache2/content.popularliving.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com content.popularliving.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com content.popularliving.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com content.popularliving.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com content.popularliving.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com content.popularliving.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=content.popularliving.com -update


#www.bellautoleasing.com
rm /var/log/apache2/www.bellautoleasing.com.log
touch /var/log/apache2/www.bellautoleasing.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.bellautoleasing.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.bellautoleasing.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.bellautoleasing.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.bellautoleasing.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.bellautoleasing.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.bellautoleasing.com -update


#www.couponliving.com
rm /var/log/apache2/www.couponliving.com.log
touch /var/log/apache2/www.couponliving.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.couponliving.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.couponliving.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.couponliving.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.couponliving.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.couponliving.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.couponliving.com -update


#www.handcraftersvillage.com
rm /var/log/apache2/www.handcraftersvillage.com.log
touch /var/log/apache2/www.handcraftersvillage.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.handcraftersvillage.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.handcraftersvillage.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.handcraftersvillage.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.handcraftersvillage.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.handcraftersvillage.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.handcraftersvillage.com -update

#www.mybargaintown.com
rm /var/log/apache2/www.mybargaintown.com.log
touch /var/log/apache2/www.mybargaintown.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com www.mybargaintown.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com www.mybargaintown.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com www.mybargaintown.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com www.mybargaintown.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com www.mybargaintown.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.mybargaintown.com -update


#www.mybargaintown.com
rm /var/log/apache2/pic3400.com.log
touch /var/log/apache2/pic3400.com.log

/home/scripts/aggregateLogs.sh w1.amperemedia.com pic3400.com
/home/scripts/aggregateLogs.sh w2.amperemedia.com pic3400.com
/home/scripts/aggregateLogs.sh w3.amperemedia.com pic3400.com
/home/scripts/aggregateLogs.sh w4.amperemedia.com pic3400.com
/home/scripts/aggregateLogs.sh w5.amperemedia.com pic3400.com

/home/sites/admin.popularliving.com/html/admin/awstats/awstats.pl -config=www.pic3400.com -update
#/home/scripts/aggregateLogs.sh web6.amperemedia.com

#/home/scripts/aggregateLogs.sh s0.amperemedia.com
#/home/scripts/aggregateLogs.sh s1.amperemedia.com
#/home/scripts/aggregateLogs.sh s2.amperemedia.com
#/home/scripts/aggregateLogs.sh s3.amperemedia.com
#/home/scripts/aggregateLogs.sh s4.amperemedia.com
#/home/scripts/aggregateLogs.sh s5.amperemedia.com
#/home/scripts/aggregateLogs.sh s6.amperemedia.com

