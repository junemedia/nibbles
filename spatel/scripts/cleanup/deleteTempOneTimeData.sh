####################################################################
##  
##  This script deletes all files in the folder:
##  	/home/sites/www_popularliving_com/temp
##  where the files is more than 7 days old.
##
##  These files are Unix-time-stamped, because for the mysql
##  replication to work, the original file used must still remain on
##  the master server.
##
##  Run by Crons nightly at 1:15am.
##    This script is required in order to clear out the old (more than
##    one week) data from the temp folder located at /home/sites/...
##    .../www_popularliving_com/temp.  These files need to remain for
##    7 days because they are required by the mysql replication system.
##  
####################################################################
#!/bin/bash

find /home/sites/admin.popularliving.com/temp/tempOtData.* -mtime +7 | xargs rm -f

