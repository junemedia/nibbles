#!/bin/bash

#
#	This script sets the permissions of a given site's directory
#	644 - images
#	755 - dirs and scripts
#

#get the working dir off of the args, as $1

ifs=$IFS
IFS='\
'


pwd=$1

for i in $(ls $pwd | sort); do
	if [ -d "$pwd$i" ]; then			#then this is a dir
		$0 $pwd$i/
		chmod 0777 $pwd$i/
	else
		if [ "${i##*.}" = "sh" ]; then		#then this is a shell script
			chmod 0775 $pwd$i
		elif [ "${i##*.}" = "php" ]; then	#then this is a php script
			chmod 0775 $pwd$i
		elif [ "${i##*.}" = "gif" ]; then
			chmod 0664 $pwd$i
		elif [ "${i##*.}" = "jpg" ]; then
			chmod 0664 $pwd$i
		elif [ "${i##*.}" = "png" ]; then
			chmod 0664 $pwd$i
		elif [ "${i##*.}" = "html" ]; then
			chmod 0664 $pwd$i
		fi
	fi
done
IFS=$ifs

