<?php 

	$serverPath = 'http://default-environment-aytcrw22ze.elasticbeanstalk.com/api';

	$url = $serverPath."/notification/checkpunctual";
	header( "Refresh: 0; URL=".$url );

	echo "Redirecting to ".$url."\n ";