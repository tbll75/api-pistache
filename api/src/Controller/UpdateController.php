<?php 

namespace App\Controller;

class UpdateController extends MailController{

	public function family(){
/* La synthaxe sql ici nous arrange, on reçoit un json decode de la forme array(key => value) du coup une fois l'id trouvé, on parcours le tableau et on récupère 
une chaine de la forme "key = 'value'," On vérifiera aussi que les champs existe dans la table. Histoire de retourner une erreur qui a un sens. */
		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idFamily FROM api_Family WHERE idFamily = ".$json['idFamily'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck)){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_Family'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idFamily')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_Family SET $str WHERE idFamily = ".$json['idFamily'];
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_Family WHERE idFamily = ".$json['idFamily'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de cette famille n\'est pas enregistré !')); }

	}

	public function child(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idChildren FROM api_Children WHERE idChildren = ".$json['idChildren'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck)){

			// on cherche les noms des colonnes 
			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_Children'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idChildren')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_Children SET $str WHERE idChildren = ".$json['idChildren'];
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_Children WHERE idChildren = ".$json['idChildren'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de cet enfant n\'est pas enregistré !')); }

	}

	public function parent(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idParent FROM api_Parent WHERE idParent = ".$json['idParent'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck)){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_Parent'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idParent')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_Parent SET $str WHERE idParent = ".$json['idParent'];
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_Parent WHERE idParent = ".$json['idParent'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de ce parent n\'est pas enregistré !')); }

	}

	public function settings(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idSettings FROM api_Settings WHERE idSettings = ".$json['idSettings'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck)){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_Settings'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idSettings')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_Settings SET $str WHERE idSettings = ".$json['idSettings'];
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_Settings WHERE idSettings = ".$json['idSettings'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de ce setting n\'est pas enregistré !')); }

	}

	public function chore(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idChoreRec FROM api_ChoreRec WHERE idChoreRec = ".$json['idChoreRec'];
		$reqCheck = $this->select($sqlCheck);
		if(!empty($reqCheck[0]['idChoreRec'])){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_ChoreRec'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idChoreRec')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field) && $key != 'recMomentOfDay' && $key != 'recMomentOfWeek' && $key != 'choreChildren' && $key != 'idChoreRec'){
					$str .= $key." = '".$value."', ";
				}elseif($key == 'recMomentOfDay'){
					$str .= "matin = '".$value[0]."', dejeuner = '".$value[1]."', gouter = '".$value[2]."', diner = '".$value[3]."', ";
				}elseif($key == 'recMomentOfWeek'){
					$str .= "lundi = '".$value[0]."', mardi = '".$value[1]."', mercredi = '".$value[2]."', jeudi = '".$value[3]."', vendredi = '".$value[4]."', samedi = '".$value[5]."', dimanche = '".$value[6]."', ";
				}
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_ChoreRec SET $str WHERE idChoreRec = '".$json['idChoreRec']."'";
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_ChoreRec WHERE idChoreRec = ".$json['idChoreRec'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de cette tache n\'est pas enregistré !')); }

	}

	public function choredone(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idChoreDone FROM api_ChoreDone WHERE idChoreDone = ".$json['idChoreDone'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck[0]['idChoreDone'])){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_ChoreDone'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idChoreDone')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}

			$str = substr($str, 0, -2);
			$sql = "UPDATE api_ChoreDone SET $str WHERE idChoreDone = '".$json['idChoreDone']."'";
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_ChoreDone WHERE idChoreDone = ".$json['idChoreDone'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de cette tache n\'est pas enregistré !')); }

	}

	public function unlockobject(){

		$json = json_decode($_POST['json'], true);
		// ici $json est le tableau contenant toutes les infos pour la famille à update.
		// on cherche si l'id existe.
		$sqlCheck = "SELECT idObjectUnlock FROM api_ObjectUnlock WHERE idObjectUnlock = ".$json['idObjectUnlock'];
		$reqCheck = $this->select($sqlCheck);

		if(!empty($reqCheck)){

			$sqlFields = "SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'api_ObjectUnlock'";
			$repFields = $this->select($sqlFields);

			$field = "";
			foreach ($repFields as $tableField) {
				if($tableField['COLUMN_NAME'] != 'idObjectUnlock')
					$field[] = $tableField['COLUMN_NAME'];
			}

			$str = "";
			foreach ($json as $key => $value) {
				if(in_array($key, $field))
					$str .= $key." = '".$value."', ";
			}
			$str = substr($str, 0, -2);
			$sql = "UPDATE api_ObjectUnlock SET $str WHERE idObjectUnlock = ".$json['idObjectUnlock'];
			$req = $this->update($sql);

			$sqlShow = "SELECT * FROM api_ObjectUnlock WHERE idObjectUnlock = ".$json['idObjectUnlock'];
			$reqShow = $this->select($sqlShow);

			echo json_encode($reqShow[0]);

		}else{ echo json_encode(array('error' => 'l\'id de cet objet n\'est pas enregistré !')); }

	}

	public function alldata(){

		$json = json_decode($_POST['json'], true);

		if(isset($json['idFamily'])){ $idFamily = $json['idFamily']; }else{ $idFamily = ""; }
		if(isset($json['name'])){ $name = "name = '".$json['name']."', "; }else{ $name = ""; }
		// if(isset($json['masterPassword'])){ $masterPassword = $json['masterPassword']; }
		if(isset($json['mail'])){ $mail = "mail = '".$json['mail']."', "; }else{ $mail = ""; }

		$fieldsFamily = $name.$mail;
		$fieldsFamily = substr($fieldsFamily, 0, -2);

		if(!empty($fieldsFamily) && !empty($idFamily)){
			$sqlFamily = "UPDATE api_Family SET $fieldsFamily WHERE idFamily = ".$idFamily;
			$reqFamily = $this->update($sqlFamily);
		}

		//MemberList
		foreach ($json['MemberList'] as $member) {

			if(isset($member['idChildren'])){ $idChildren = $member['idChildren']; }else{ $idChildren = ""; }
			if(isset($member['name'])){ $name = "name = '".$member['name']."', "; }else{ $name = ""; }
			if(isset($member['sex'])){ $sex = "sex = '".$member['sex']."', "; }else{ $sex = ""; }
			if(isset($member['birthday'])){ $birthday = "birthday = '".$member['birthday']."', "; }else{ $birthday = ""; }
			if(isset($member['photo'])){ $photo = "photo = '".$member['photo']."', "; }else{ $photo = ""; }
			if(isset($member['Type'])){ $type = "Type = '".$member['Type']."', "; }else{ $type = ""; }
			if(isset($member['level'])){ $level = "level = '".$member['level']."', "; }else{ $level = ""; }
			if(isset($member['xp'])){ $xp = "xp = '".$member['xp']."', "; }else{ $xp = ""; }
			if(isset($member['energy'])){ $energy = "energy = '".$member['energy']."', "; }else{ $energy = ""; }
			if(isset($member['nbBanana'])){ $nbBanana = "nbBanana = '".$member['nbBanana']."', "; }else{ $nbBanana = ""; }
			if(isset($member['activateTuto']) && $member['activateTuto'] != ''){ $activateTuto = "activateTuto = '".$member['activateTuto']."', "; }else{ $activateTuto = ""; }

			$fieldsMember = $name.$sex.$birthday.$photo.$type.$level.$xp.$energy.$nbBanana.$activateTuto;
			$fieldsMember = substr($fieldsMember, 0, -2);

			if(!empty($fieldsMember) && !empty($idChildren)){
				$sqlMember = "UPDATE api_Children SET $fieldsMember WHERE idChildren = ".$idChildren;
				$reqMember = $this->update($sqlMember);
			}

			//RecurentChoreList
			foreach ($member['RecurentChoreList'] as $chore) {
				if(isset($chore['idChoreRec'])){ $idChoreRec = 'idChoreRec = '.$chore['idChoreRec']; }else{ $idChoreRec = ""; }
				if(isset($chore['xpToWin'])){ $xpToWin = "xpToWin = '".$chore['xpToWin']."', "; }else{ $xpToWin = ""; }
				if(isset($chore['name'])){ $name = "name = '".$chore['name']."', "; }else{ $name = ""; }
				if(isset($chore['text'])){ $text = "text = '".$chore['text']."', "; }else{ $text = ""; }
				if(isset($chore['state'])){ $state = "state = '".$chore['state']."', "; }else{ $state = ""; }
				if(isset($chore['imageName'])){ $imageName = "imageName = '".$chore['imageName']."', "; }else{ $imageName = ""; }
				if(isset($chore['energy'])){ $energy = "energy = '".$chore['energy']."', "; }else{ $energy = ""; }
				if(isset($chore['validation'])){ $validation = "validation = '".$chore['validation']."', "; }else{ $validation = ""; }
				if(isset($chore['date'])){ $date = "date = '".$chore['date']."', "; }else{ $date = ""; }
				if(isset($chore['isRecurent'])){ $isRecurent = "isRecurent = '".$chore['isRecurent']."', "; }else{ $isRecurent = ""; }


				//recMomentOfDay
				$setMomentDay = "";
				foreach ($chore['recMomentOfDay'] as $momentOfDayAttr => $momentOfDayVal) {
					if($momentOfDayVal === false){ $momentOfDayVal = 0; }
					if($momentOfDayVal === true){ $momentOfDayVal = 1; }
					switch ($momentOfDayAttr) {
						case '0':
							$momentOfDayAttr = 'matin';
							break;
						case '1':
							$momentOfDayAttr = 'dejeuner';
							break;
						case '2':
							$momentOfDayAttr = 'gouter';
							break;
						case '3':
							$momentOfDayAttr = 'diner';
							break;
						
						default:
							$momentOfDayAttr = '';
							break;
					}
					if(!empty($momentOfDayAttr))
						$setMomentDay .= $momentOfDayAttr." = '".$momentOfDayVal."', ";
				}
				if(!empty($setMomentDay))
					$recMomentOfDay = substr($setMomentDay, 0, -2).", ";

				//recMomentOfWeek
				$setMomentWeek = "";
				foreach ($chore['recMomentOfWeek'] as $momentOfWeekAttr => $momentOfWeekVal) {
					if($momentOfWeekVal === false){ $momentOfWeekVal = 0; }
					if($momentOfWeekVal === true){ $momentOfWeekVal = 1; }
					switch ($momentOfWeekAttr) {
						case '0':
							$momentOfWeekAttr = 'lundi';
							break;
						case '1':
							$momentOfWeekAttr = 'mardi';
							break;
						case '2':
							$momentOfWeekAttr = 'mercredi';
							break;
						case '3':
							$momentOfWeekAttr = 'jeudi';
							break;
						case '4':
							$momentOfWeekAttr = 'vendredi';
							break;
						case '5':
							$momentOfWeekAttr = 'samedi';
							break;
						case '6':
							$momentOfWeekAttr = 'dimanche';
							break;
						
						default:
							$momentOfWeekAttr = '';
							break;
					}
					if(!empty($momentOfWeekAttr))
						$setMomentWeek .= $momentOfWeekAttr." = '".$momentOfWeekVal."', ";
				}
				if(!empty($setMomentWeek))
					$recMomentOfWeek = substr($setMomentWeek, 0, -2);

				$recChoreAttr =  $xpToWin.$name.$text.$state.$imageName.$energy.$validation.$date.$isRecurent.$recMomentOfDay.$recMomentOfWeek;

				if(!empty($recChoreAttr)){
					$sqlChoreRec = "UPDATE api_ChoreRec SET $recChoreAttr WHERE ".$idChoreRec;
					$reqChoreRec = $this->update($sqlChoreRec);
				}


			}

			//Hero
			$setHero = "";
			foreach ($member['hero'] as $heroAttr => $heroVal) {
				if(!empty($heroVal))
					$setHero .= $heroAttr." = '".$heroVal."', ";
			}
			$setHero = substr($setHero, 0, -2);
			if(!empty($setHero)){
				$sqlHero = "UPDATE api_Hero SET $setHero WHERE idChildren = $idChildren";
				$reqHero = $this->update($sqlHero);
			}

			//listeDebloque
			foreach($member['listeDebloque'] as $debloque) {
				$idObject = $debloque;
			}

			//Settings 
			$setSetting = "";
			foreach ($member['Settings'] as $settingAttr => $settingVal) {
				if(!empty($settingVal))
					$setSetting .= $settingAttr." = '".$settingVal."', ";
			}
			$setSetting .= substr($setSetting, 0, -2);
			if(!empty($setSetting)){
				$sqlSetting = "UPDATE api_Settings SET $setSetting WHERE idChildren = $idChildren";
				$reqSetting = $this->update($sqlSetting);
			}
		}
		echo 'true';
		return 'true';

	}

	public function askPass($hashmail){
		// on récupere les mails
		$req = $this->select("SELECT mail FROM Family");
		echo '<pre>';
		print_r($req);
		echo '</pre>';

		die();
		// on compare dans la liste de la bdd
		if(in_array($hashmail, $mails)){

		}else{
			echo '{"error":"Email invalide"}';
			die();
		}
	}

	public function pass(){
		// On check les données
		$json = json_decode($_POST['json'], true);
		if(!empty($json['mail']))
			$mail = $json['mail'];
		else{
			echo '{"error":"pas de mail.", "code":"1"}';
			die();
		}
		if(!empty($json['password']))
			$pass = $json['password'];

		// Si pas de mdp, on le génère
		if($newPass == null){
			$nb_car = 6;
			$chaine = 'azertyuiopqsdfghjklmwxcvbn';
		    $nb_lettres = strlen($chaine) - 1;
		    for($i=0; $i < $nb_car; $i++)
		    {
		        $pos = mt_rand(0, $nb_lettres);
		        $car = $chaine[$pos];
		        $newPass .= $car;
		    }
		}
		// on hash le mdp avec la clé secret.
		$pass = hash_hmac('sha256', $newPass, 'secret', false);
		// on update le mdp hashé.
		$rep = $this->update("UPDATE api_family SET masterPassword = '$pass' WHERE mail = ".$mail);

		// on envoit le mail avec le mdp.
		$this->newPass($mail, $masterPassword);
	}

}

?>