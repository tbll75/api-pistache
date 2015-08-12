<?php 

namespace App\Controller;

class NotificationController extends SQLController{

	/**
	methode filtre temporel
	**/

	public function recurrent(){

		date_default_timezone_set('Europe/Paris');

		$momentOfWeek = date('N') - 1;
		$Hour = date('G');

		echo 'Jour : '.$momentOfWeek."<br/>Heure : ".$Hour."<br/><br/>";

		// traitement horloge
		$momentOfDay = 'none';
		if( 7 < $Hour && $Hour < 9){
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
			curl_setopt( $ch,CURLOPT_URL, $_SERVER['SERVER_NAME'].'/notification/checkmission' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, array('json' => $json) );
			$result = curl_exec($ch );
			curl_close( $ch );
		}else{
			echo 'Pas de notification lancée pour les tâches recurrentes !<br/><br/>Error : <i>momentOfDay</i> n\'est pas definie.<br/>Message : Ce n\'est pas le moment.';
		}
	}
	public function tryme(){
		
		date_default_timezone_set('Europe/Paris');

		$momentOfWeek = date('N') - 1;
		$Hour = date('G');

		echo 'Jour : '.$momentOfWeek."<br/>Heure : ".$Hour."<br/><br/>";

		// traitement horloge
		$momentOfDay = 'none';
		if( 7 < $Hour && $Hour < 12){
			$momentOfDay = '0';
		}if( 11 < $Hour && $Hour < 16){
			$momentOfDay = '1';
		}if( 15 < $Hour && $Hour < 19){
			$momentOfDay = '2';
		}if( 18 < $Hour && $Hour < 20){
			$momentOfDay = '3';
		}

		$json = '{"momentOfDay":"'.$momentOfDay.'","momentOfWeek":"'.$momentOfWeek.'"}';

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, $_SERVER['SERVER_NAME'].'/notification/checkmission' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, array('json' => $json) );
		$result = curl_exec($ch );
		curl_close( $ch );
	}

	public function punctual(){

		date_default_timezone_set('Europe/Paris');

		$Hour = date('G');

		if(10 < $Hour && $Hour < 12){
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, $_SERVER['SERVER_NAME'].'/notification/checkpunctual' );
			curl_setopt( $ch,CURLOPT_POST, true );
			// curl_setopt( $ch,CURLOPT_POSTFIELDS, array('json' => $json) );
			$result = curl_exec($ch );
			curl_close( $ch );
		}else{
			echo 'Pas de notification lancée pour les tâches ponctuelles !<br/><br/>Error : <i>Hour</i> ne correspond pas au créneau.<br/>Message : Ce n\'est pas le moment.';
		}
	}


	/**
	methode de traitement des notifs
	**/

	// Objectif : Quand on envoit en post a l'API le serveur envoit la notif aux bons supports de la famille.

	public function checkpunctual(){
		// ici pas de json, on va regarder la date du jour, et voir si en bdd on trouve la même chose.
		// current date
		echo 'Nous sommes le : '.date('Y-m-d')."<br/>";
		//date des taches
		$sqlChore = "SELECT idChoreRec, childId, name, date FROM api_ChoreRec WHERE isActive = 1 AND date IS NOT NULL AND isRecurrent = 0";
		$reqChore = $this->select($sqlChore);

		// on crée deux tableaux : un contenant les idChild et l'autre les missions et leur détail
		$children = array();
		foreach ($reqChore as $choreRec) {
			$date = preg_replace("/[^0-9]/","",$choreRec['date']);
			$date = substr($date, 0, 10);
			$date = date('Y-m-d', $date);
			$child = $choreRec['childId'];
			// on explode 
			$children = explode(', ', $child);
			// on rentre les ids
			foreach ($children as $chilId) {
				if($date == date('Y-m-d') && !empty($chilId) && !in_array($chilId, $children)){
					$children[] = $chilId;
				}
			}
		}


		$childrenToNotif = array(); // tableau contenant tous les enfant qui ont des notif et le detail des missions.
		foreach ($children as $child) {
			$childToNotif = array(); // tableau contenant les mission et details de chaque gosse.
			$sqlChild = "SELECT idChildren, name, Family_idFamily FROM api_Children WHERE idChildren = ".$child;
			$reqChild = $this->select($sqlChild);
			// on rentre les données de l'enfant dans le tableau.
			$childToNotif['id'] = $reqChild[0]['idChildren'];
			$childToNotif['name'] = $reqChild[0]['name'];
			$childToNotif['idFamily'] = $reqChild[0]['Family_idFamily'];
			$childToNotif['nbChore'] = 0;
			// on fait le foreach de chaque tache a rentrer.
			foreach ($reqChore as $punctualChore) {
				$date = preg_replace("/[^0-9]/","",$punctualChore['date']);
				$date = substr($date, 0, 10);
				$date = date('Y-m-d', $date);
				if($date == date('Y-m-d') && $punctualChore['Children_idChildren'] == $child){
					$childToNotif['chore'][] = $punctualChore; 
					$childToNotif['nbChore']++;
				}

			}
			// on rentre la nouvelle entrée dans le tableau général.
			$childrenToNotif[] = $childToNotif;
		}
		
		$nbNotifSend = 0;
		$totalNotif = 0;
		foreach ($childrenToNotif as $childToNotif) {
			if($childToNotif['nbChore'] >= 2){
				// Notif de type "Aujourd'hui tu as X missions en attente"
				$result = $this->lotOfMissions($childToNotif['name'], $childToNotif['nbChore'], 3, $childToNotif['idFamily']);
				// si c'est tout bon on upadte le ChoreRec en isNOTactive
				if($result == false){ echo ' : <font color="red">error</font>'; }else{
					foreach ($childToNotif['chore'] as $chore) {
						$sqlUpChore = "UPDATE api_ChoreRec SET isActive = 0 WHERE idChoreRec = ".$chore['idChoreRec'];
						$this->update($sqlUpChore);
					}
				}
				$nbNotifSend++;
				$totalNotif += $childToNotif['nbChore'];
			}elseif($childToNotif['nbChore'] == 1){
				// Notif de type "N'oublis pas ta mission : {titreMission}"
				$result = $this->oneMission($childToNotif['name'], $childToNotif['chore'][0]['name'], 4, $childToNotif['idFamily']);
				// si c'est tout bon on upadte le ChoreRec en isNOTactive
				if($result == false){ echo ' : <font color="red">error</font>'; }else{
					$sqlUpChore = "UPDATE api_ChoreRec SET isActive = 0 WHERE idChoreRec = ".$childToNotif['chore'][0]['idChoreRec'];
					$this->update($sqlUpChore);
				}
				$nbNotifSend++;
				$totalNotif += $childToNotif['nbChore'];
			}
		}

		echo '<br/><br/>Toutes les notification à envoyer on été envoyées.<br/>Nombre de notification : '.$nbNotifSend."<br/>Nombre total d'alerte : ".$totalNotif;

	}
	
	public function checkmission(){

		$json = json_decode($_POST['json'], true);

		// Prend deux parametres : jour et moment de la journée
		$moments = array('0', '1', '2', '3');
		$jours = array('0', '1', '2', '3', '4', '5', '6');
		if(!in_array($json['momentOfDay'], $moments)){ echo 'pas de periode'; die(); }else{ $momentOfDay = $json['momentOfDay']; }
		if(!in_array($json['momentOfWeek'], $jours)){ echo 'pas de jour'; die(); }else{ $momentOfWeek = $json['momentOfWeek']; }

		switch ($momentOfDay) {
			case '0':
				$momentOfDay = 'matin = 1';
				$textOfMoment = "ce matin";
				break;
			case '1':
				$momentOfDay = 'dejeuner = 1';
				$textOfMoment = "ce midi";
				break;
			case '2':
				$momentOfDay = 'gouter = 1';
				$textOfMoment = "pour le goûter";
				break;
			case '3':
				$momentOfDay = 'diner = 1';
				$textOfMoment = "ce soir";
				break;
			
			default:
				$momentOfDay = "";
				break;
		}
		switch ($momentOfWeek) {
			case '0':
				$momentOfWeek = 'lundi = 1';
				break;
			case '1':
				$momentOfWeek = 'mardi = 1';
				break;
			case '2':
				$momentOfWeek = 'mercredi = 1';
				break;
			case '3':
				$momentOfWeek = 'jeudi = 1';
				break;
			case '4':
				$momentOfWeek = 'vendredi = 1';
				break;
			case '5':
				$momentOfWeek = 'samedi = 1';
				break;
			case '6':
				$momentOfWeek = 'dimanche = 1';
				break;
			
			default:
				$momentOfWeek = "";
				break;
		}
		$conditions = implode(' AND ', array($momentOfWeek, $momentOfDay));
		// On fouille dans ChoreRec si des missions correspondent à ce crénau. On fait un tableau des ids resultants.
		$sqlGetChoreRec = "SELECT * FROM api_ChoreRec WHERE ".$conditions." AND isActive = 1";
		$reqGetChoreRec = $this->select($sqlGetChoreRec);

		// On récupère les ids enfant correspondant aux entrées missions.
		$notifchildren = array();
		foreach ($reqGetChoreRec as $choreRec) {
			$child = $choreRec['childId'];
			// on explode 
			$children = explode(', ', $child);
			// on rentre les ids
			foreach ($children as $chilId) {
				if(!empty($chilId) && !in_array($chilId, $notifchildren)){
					$notifchildren[] = $chilId;
				}
			}
		}

		// On croise les deux tableaux : on veut par enfant le nombre de missions qu'il a (et détail de mission).

		if(!empty($notifchildren)){

			$childChore = array();
			foreach ($notifchildren as $notifchild) {

				// Pour chaque enfant on cherche son nom et sa famille.
				$sqlName = "SELECT name, Family_idFamily FROM api_Children WHERE idChildren = ".$notifchild;
				$reqName = $this->select($sqlName);
				$childName = $reqName[0]['name'];
				$idFamily = $reqName[0]['Family_idFamily'];

				$childChoreData = array();
				$childChoreData['name'] = $childName;
				$childChoreData['idFamily'] = $idFamily;
				$childChoreData['nbChore'] = 0;

				foreach ($reqGetChoreRec as $choreRec) {
					$notifchildren = $choreRec['childId'];
					$notifchildren = explode(', ', $notifchildren);
					foreach ($notifchildren as $childID) {
						if($notifchild == $childID){
							$childChoreData['chore'][] = $choreRec;
							$childChoreData['nbChore']++;
						}
					}
					
				}

				$childChore[] = $childChoreData;
			}

			// A ce niveau le tableau est construit pour le voir :
			/*
			echo '<pre>';
			print_r($childChore);
			echo '</pre>';
			*/
			// on envoit vers la bonne notif.
			foreach ($childChore as $childToNotif) {
				if($childToNotif['nbChore'] >= 2){
					// Notif de type "tu as X missions en attente"
					$this->lotOfMissions($childToNotif['name'], $childToNotif['nbChore'], 1, $childToNotif['idFamily'], $textOfMoment);
				}elseif($childToNotif['nbChore'] == 1){
					// Notif de type "N'oublis pas ta mission : {titreMission}"
					$this->oneMission($childToNotif['name'], $childToNotif['chore'][0]['name'], 2, $childToNotif['idFamily'], $textOfMoment);
				}
			}
		}else{
			echo 'no child found\n';
		}

	}

	public function lotOfMissions($name, $nbMission, $idNotification, $idFamily, $momentDay = "aujourd'hui"){

		// D'abord on récupère les infos de la notif
		$sqlnotif = "SELECT * FROM api_Notification WHERE idNotification = ".$idNotification;
		$reqnotif = $this->select($sqlnotif); // la notif etant unique on peut directement passer a la ligne suivante, sans boucle.
		$notifInfo = $reqnotif[0];

		preg_match_all('#{[a-zA-Z]+}#', $notifInfo['text'], $valeurs); // tableau contenant toutes les variables de la notification
		if(!empty($valeurs[0])){ // OUI!
			$notifInfo['text'] = str_replace($valeurs[0][0], $name, $notifInfo['text']);
			$notifInfo['text'] = str_replace($valeurs[0][1], $momentDay, $notifInfo['text']);
			$notifInfo['text'] = str_replace($valeurs[0][2], $nbMission, $notifInfo['text']);
		}

		// Une fois la notif prète, on récupère les infos de supports visés
		$sqlsupp = "SELECT deviceToken, os FROM api_Support WHERE Family_idFamily = ".$idFamily;
		$reqsupp = $this->select($sqlsupp);

		// on envoit en executant le script php avec les bon paramètres (message et support) pour chaque support visé.
		foreach ($reqsupp as $support){
			if($support['os'] == 'ios'){
				$validIos = $this->iosSend($support['deviceToken'], "Pistache t'attend !", $notifInfo['text']);
				if($validIos){ // Si c'est valide, on envoit à la bdd (ca va permettre ensuite de compte le nombre de notif par device, et donc d'afficher le bon numero)
					$this->insert("INSERT INTO api_FamilyNotification (Family_idFamily, Notification_idNotification, deviceToken) VALUES ('$idFamily', '$idNotification', '".$support['deviceToken']."')");
				}else{ $error[] = 1; }
			}
			elseif($support['os'] == 'android'){
				$validAndroid = $this->androidSend($support['deviceToken'], "Pistache t'attend !", $notifInfo['text']);
				if($validAndroid){ // Si c'est valide, on envoit à la bdd (ca va permettre ensuite de compte le nombre de notif par device, et donc d'afficher le bon numero)
					$this->insert("INSERT INTO api_FamilyNotification (Family_idFamily, Notification_idNotification, deviceToken) VALUES ('$idFamily', '$idNotification', '".$support['deviceToken']."')");
				}else{ $error[] = 1; }
			}
		}

		if(empty($error)){
			return true;
		}else{
			return false;
		}

	}

	public function oneMission($name, $missionName, $idNotification, $idFamily, $momentDay = "aujourd'hui"){

		// D'abord on récupère les infos de la notif
		$sqlnotif = "SELECT * FROM api_Notification WHERE idNotification = ".$idNotification;
		$reqnotif = $this->select($sqlnotif); // la notif etant unique on peut directement passer a la ligne suivante, sans boucle.
		$notifInfo = $reqnotif[0];

		preg_match_all('#{[a-zA-Z]+}#', $notifInfo['text'], $valeurs); // tableau contenant toutes les variables de la notification
		if(!empty($valeurs[0])){ // OUI!
			$notifInfo['text'] = str_replace($valeurs[0][0], $name, $notifInfo['text']);
			$notifInfo['text'] = str_replace($valeurs[0][1], $momentDay, $notifInfo['text']);
			$notifInfo['text'] = str_replace($valeurs[0][2], $missionName, $notifInfo['text']);
		}

		// Une fois la notif prète, on récupère les infos de supports visés
		$sqlsupp = "SELECT deviceToken, os FROM api_Support WHERE Family_idFamily = ".$idFamily;
		$reqsupp = $this->select($sqlsupp);

		// on envoit en executant le script php avec les bon paramètres (message et support) pour chaque support visé.
		foreach ($reqsupp as $support){
			if($support['os'] == 'ios'){
				$validIos = $this->iosSend($support['deviceToken'], "Pistache t'attend !", $notifInfo['text']);
				if($validIos){ // Si c'est valide, on envoit à la bdd (ca va permettre ensuite de compte le nombre de notif par device, et donc d'afficher le bon numero)
					$this->insert("INSERT INTO api_FamilyNotification (Family_idFamily, Notification_idNotification, deviceToken) VALUES ('$idFamily', '$idNotification', '".$support['deviceToken']."')");
				}else{ $error[] = 1; }
			}
			elseif($support['os'] == 'android'){
				$validAndroid = $this->androidSend($support['deviceToken'], "Pistache t'attend !", $notifInfo['text']);
				if($validAndroid){ // Si c'est valide, on envoit à la bdd (ca va permettre ensuite de compte le nombre de notif par device, et donc d'afficher le bon numero)
					$this->insert("INSERT INTO api_FamilyNotification (Family_idFamily, Notification_idNotification, deviceToken) VALUES ('$idFamily', '$idNotification', '".$support['deviceToken']."')");
				}else{ $error[] = 1; }
			}
		}

		if(empty($error)){
			return true;
		}else{
			return false;
		}

	}

	public function clear(){
		$json = json_decode($_POST['json'], true);
		$deviceToken = $json['deviceToken'];

		$reqGetOs = $this->select("SELECT os FROM api_Support WHERE deviceToken = '".$deviceToken."' LIMIT 1");

		if($reqGetOs[0]['os'] == 'ios'){
			$validIos = $this->iosClear($deviceToken);
			if($validIos)
				$this->delete("DELETE FROM api_FamilyNotification WHERE deviceToken = '".$deviceToken."'");
		}
		elseif($reqGetOs[0]['os'] == 'android'){
			$validAndoid = $this->androidClear($deviceToken);
			if($validAndroid)
				$this->delete("SELECT FROM api_FamilyNotification WHERE deviceToken = '".$deviceToken."'");
		}
	}










/**
// FONCTION DE TRAITEMENT 
*/

	public function iosSend($deviceToken, $title, $message){



		echo '<br/><br/>iOS';

		// On compte cb de notif il y a a afficher sur l'icon (badge)
		$sqlNbNotif = "SELECT COUNT(idFam_Notif) as nbNotif FROM api_FamilyNotification WHERE deviceToken = '".$deviceToken."'";
		$reqNbNotif = $this->select($sqlNbNotif);

		$nbNotif = $reqNbNotif[0]['nbNotif'] + 1;

		// Put your device token here (without spaces):
		// $deviceToken = '7b811ae2fc509af1ae85f6234743c2b795d9b060a7eef4bb52465b909b4394c9';


		// Put your private key's passphrase here:
		$passphrase = 'Bob110891'; // prod
		// $passphrase = 'pistache42'; // dev

		// Put your alert message here:
		// $message = 'Bonjour !';

		////////////////////////////////////////////////////////////////////////////////

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', '../ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		$fp = stream_socket_client(
			'ssl://gateway.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);

		// Create the payload body
		$body['aps'] = array(
			'alert' => array('body' => $message, 'title' => $title),
			'sound' => 'default',
			'badge' => $nbNotif
			);

		// Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		// Close the connection to the server
		fclose($fp);

		echo '<br/>';
		print_r($result);

		if (!$result){
			return false;
			// echo 'Message not delivered' . PHP_EOL;
		}else{
			return true;
			// echo 'Message successfully delivered<br/>' . PHP_EOL;
		}	

	}


	public function androidSend($deviceToken, $title, $message){

		echo '<br/><br/>Android';

		// On compte cb de notif il y a a afficher sur l'icon (badge)
		$sqlNbNotif = "SELECT COUNT(idFam_Notif) as nbNotif FROM api_FamilyNotification WHERE deviceToken = '".$deviceToken."'";
		$reqNbNotif = $this->select($sqlNbNotif);

		$nbNotif = $reqNbNotif[0]['nbNotif'] + 1;

		// project Number : 759604048159
		// SHA1 : C0:81:C1:92:46:65:A0:4E:30:D5:2B:ED:6C:98:32:80:C1:E0:04:62;com.appinest.pistache
		// clé de l'API : AIzaSyDKPInEtvNUi6oXiglPp1OwDK2B9SusiRU

		// API access key from Google API's Console
		//define( 'API_ACCESS_KEY', 'AIzaSyDKPInEtvNUi6oXiglPp1OwDK2B9SusiRU' ); //cle android
		define( 'API_ACCESS_KEY', 'AIzaSyCqOnRxHXukk-MzVnjO_a2gwMHR087osQY' ); //cle server
		//define( 'API_ACCESS_KEY', '759604048159' );

		$registrationIds = array($deviceToken);
		//$registrationIds = array("APA91bGjfYDAz8c-9eI6JNOpbQV6qswpuTG5okT0ChYtrLTgOKUwDjthkazzuPPO0W_A6vTfWk-RbrFfSERuVNqDFg9tozH_3Z5GgNLUtXU5Pi7l-5t45sJ2naVis3VGEUj8-m44LeX-oc6KvGxD8194ub7lT2wgqZK6Ey09wOC6Ixa-Y0hfRP4");
		$icon = 'app_icon';
		$fields = array
		(
		    'registration_ids'  => $registrationIds,
		    'data'              => array("alert" => $message, 
	    								"title" => $title, /*
	    								"smallIcon" => $icon, 
	    								"largeIcon" => $icon, 
	    								"icon" => $icon, */
	    								"badge" => 1, 
	    								"msgcnt" => $nbNotif
	    								)
		);

		$headers = array
		(
		    'Authorization: key=' . API_ACCESS_KEY,
		    'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );

		echo '<br/>';
		print_r($result);

		if(json_decode($result, true)['success'] == 1){
			return true;
		}else{
			return false;
		}

	}

	public function iosClear($deviceToken){

		echo '<br/>Sending Ios Norif';

		// Put your device token here (without spaces):
		// $deviceToken = '7b811ae2fc509af1ae85f6234743c2b795d9b060a7eef4bb52465b909b4394c9';

		// Put your private key's passphrase here:
		$passphrase = 'Bob110891';

		// Put your alert message here:
		// $message = 'Bonjour !';

		////////////////////////////////////////////////////////////////////////////////

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', '../ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);

		// echo 'Connected to APNS<br/>' . PHP_EOL;

		// Create the payload body
		$nbNotif = 0;
		$body['aps'] = array(
			'alert' => array('body' => 'On nettoie', 'title' => 'Netoyage'),
			'badge' => $nbNotif
			);

		// Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		// Close the connection to the server
		fclose($fp);

		if (!$result){ 
			return false;
			// echo 'Message not delivered' . PHP_EOL;
		}else{ 
			return true;
			// echo 'Message successfully delivered<br/>' . PHP_EOL;
		}

	}

	public function androidClear($deviceToken){

	}


}


?>