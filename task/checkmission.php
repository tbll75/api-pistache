<?php

	$serverPath = 'http://default-environment-aytcrw22ze.elasticbeanstalk.com/api';

	$momentOfWeek = date('N') - 1;
	$Hour = date('G');

	echo 'Jour : '.$momentOfWeek."<br/>Heure : ".$Hour."<br/><br/>";

	// traitement horloge
	if( 8 < $Hour && $Hour < 10){
		$momentOfDay = '0';
	}if( 11 < $Hour && $Hour < 13){
		$momentOfDay = '1';
	}if( 15 < $Hour && $Hour < 17){
		$momentOfDay = '2';
	}if( 18 < $Hour && $Hour < 20){
		$momentOfDay = '3';
	}

	if($momentOfDay == '0' || $momentOfDay == '1' || $momentOfDay == '2' || $momentOfDay == '3'){
		// Et un p'tit coup de cURL, un !!
		echo $json = '{"momentOfDay":"'.$momentOfDay.'","momentOfWeek":"'.$momentOfWeek.'"}';
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, $serverPath.'/notification/checkmission' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, array('json' => $json) );
		$result = curl_exec($ch );
		curl_close( $ch );
	}else{
		echo 'momentOfDay n\'est pas definie : Pas de notification lancée pour les tâches recurrentes !<br/>';
	}

