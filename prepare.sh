#!/bin/sh

php --help > /dev/null 2>&1

if [ $? -eq 0 ] ; then

	php magserver/pushserv/checkos.php

	if [ $? -eq 0 ] ; then

		for dir in magserver/local magtest/log
		do
			mkdir -p $dir
			chmod -R 777 $dir
		done

		cp magtest/config.php.example magtest/config.php

		echo "Prepare environment success..."

	fi

else

	echo "Cannot find \"php\" in executive paths!"
	echo "Quit..."

fi
