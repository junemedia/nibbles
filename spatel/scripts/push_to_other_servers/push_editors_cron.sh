#!/bin/bash

#
#	stats a file every 5 minutes. 
#	if the file is there, run the editor's push
#

asdf=`stat /home/sites/admin.popularliving.com/html/admin/pushForEditors/flag`

#echo $asdf

if [ "$asdf" != "" ]; then
	#echo "asdf is true"
	/home/scripts/push_to_other_servers/push_popularliving_editors_and_bannerFarm_to_web1-4_and_w0-5.sh
fi

rm /home/sites/admin.popularliving.com/html/admin/pushForEditors/flag
