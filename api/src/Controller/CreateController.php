<?php 

namespace App\Controller;

class CreateController extends MailController{

	public function family(){ 
		
		$json = json_decode($_POST['json'], true);
		// On vérifie que les infos necessaires sont passées.
		if(empty($json['mail'])){
				echo json_encode(array('error' => 'Aucun mail n\'est passé.', 'code' => '0')); // pas de mail entré
		}elseif(empty($json['masterPassword'])){
				echo json_encode(array('error' => 'Aucun nom n\'est passé.', 'code' => '1')); // pas de nom entré
		}else{
		 // select pour les emails puis pregmatch avec la valeur du champ mail, puis si tout vas bien on fait l'ajout.
			$sql = "SELECT mail FROM api_Family";
			// instancie un element de requete
			$rep = $this->select($sql);
			// on nettoie la requete
			foreach ($rep as $m) {
				$mails[] = $m['mail'];
			}
			// on compare les mails.
			if(!empty($mails) && in_array($json['mail'], $mails)){
				echo json_encode(array('error' => 'Le mail est deja; enregistré.', 'code' => '2')); // mail déjà connu
			}else{
				$masterPassword = $json['masterPassword'];
				$mail = $json['mail'];
				// On envoit le mail qui va bien !
				$this->welcome($mail, $masterPassword);
				// On crypte le mdp
				$pass = hash_hmac('sha256', $masterPassword, 'secret', false); // sha256 est un algo de cryptage plutot badass, et 'secret' c'est la clé de cryptage a partager.
				$sql = "INSERT INTO api_Family  (mail, masterPassword) VALUES ('$mail', '$pass')";
				$rep = $this->insert($sql);

				// maintenant on va renvoyer l'id de cette famille
				$sql2 = "SELECT MAX(idFamily) AS lastId FROM api_Family";
				$rep2 = $this->select($sql2);
				$idFamily = $rep2[0]['lastId'];

				// On enregistre le(s) device(s) lié(s) à cette nouvelle famille.
				if(!empty($json['device'])){
					$device = $json['device'];
					if(!empty($device['deviceToken'])){
						$deviceToken = $device['deviceToken'];
						$os = $device['os'];

						/**
						// Vérifier que le token existe pas, si oui, supprimer les entrées, et ensuite la suite (insertion du support lié à une famille). 
						**/

						$sqldevice = "INSERT INTO api_Support (Family_idFamily, os, deviceToken) VALUES ('$idFamily', '$os', '$deviceToken')";
						$reqdevice = $this->insert($sqldevice);
					}
				}
				// return 
				$json = $rep2[0];
				echo json_encode($json); // on retourne l'id de la toute nouvelle famille pour ensuite l'enregistrer en local.
			}
		}
	}

	public function child(){
		$json = json_decode($_POST['json'], true);

		if(empty($json['name'])){
				echo json_encode(array('error' => 'Aucun nom n\'est passe.')); // pas de nom entré
		}elseif(empty($json['birthday'])){
				echo json_encode(array('error' => 'Aucune date n\'est passe.')); // pas de date entrée
		}elseif(empty($json['Family_idFamily'])){
				echo json_encode(array('error' => 'Aucun id de famille n\'est passe.')); // pas d'idFamily entrée
		}elseif(empty($json['sex']) && $json['sex'] != '0'){
				echo json_encode(array('error' => 'Aucun sexe n\'est passe.')); // pas de sexe entrée
		}else{
			$idFamily = $json['Family_idFamily'];
			$nom = htmlspecialchars($json['name']);
			$date = $json['birthday'];
			$sexe = $json['sex'];
			$level = $json['level'];
			$xp = $json['xp'];
			$energy = $json['energy'];
			$nbBanana = $json['nbBanana'];

			if(!isset($json['photo'])){ $photo = 'none'; }else{ $photo = $json['photo']; }
			// Maintenant on chope les noms des enfants de la famille, et on compare pour éviter le doublon.
			$sqlenfant = "SELECT name FROM api_Children WHERE Family_idFamily = ".$idFamily;
			$repenfant = $this->select($sqlenfant);
			// on rentre dans un taleau
			foreach ($repenfant as $e) {
				$enfants[] = strtolower($e['name']);
			}
			// on compare les noms.
			if(!empty($repenfant) && in_array(strtolower($nom), $enfants)){
				echo json_encode(array('error' => 'Le compte de cet enfant est dej&agrave; enregistre.')); // enfant déjà connu pour cette famille
			}else{
				// ici on a passé tous les filtres
				// On rentre l'enfant.
				$sql = "INSERT INTO api_Children (Family_idFamily, name, birthday, sex, photo, level, xp ,energy, nbBanana) VALUES ('$idFamily', '$nom', '$date', '$sexe', '$photo', '$level', '$xp' ,'$energy', '$nbBanana')";
				$rep = $this->insert($sql);
				// On recupère la derniere entrée, pour avoir l'id de l'enfant
				$sqlget = "SELECT * FROM api_Children ORDER BY idChildren DESC LIMIT 1";
				$repget = $this->select($sqlget);
				// on cré en premier la data du hero 
				$sqlHero = "INSERT INTO api_Hero (Children_idChildren) VALUES ('".$repget[0]['idChildren']."')";
				$this->insert($sqlHero);
				// on cré en premier la data du setting 
				if(!empty($_POST['validation'])){ $validationAttr = "validation, "; $validationVal = "'".$_POST['validation'].", "; }else{ $validationAttr = ""; $validationVal = ""; }
				if(!empty($_POST['MaxPlayTime'])){ $MaxPlayTimeAttr = "MaxPlayTime, "; $MaxPlayTimeVal = "'".$_POST['MaxPlayTime'].", "; }else{ $MaxPlayTimeAttr = ""; $MaxPlayTimeVal = ""; }
				$settingAttr = substr("Children_idChildren, ".$validationAttr.$MaxPlayTimeAttr, 0, -2);
				$settingVal = substr($repget[0]['idChildren'].", ".$validationVal.$MaxPlayTimeVal, 0, -2);
				$sqlSettings = "INSERT INTO api_Settings ($settingAttr) VALUES ($settingVal)";
				$this->insert($sqlSettings);
				// On donne l'id de l'enfant.
				echo json_encode($repget[0]);
			}
		}
	}

	public function parent(){
		$json = json_decode($_POST['json'], true);

		if(empty($json['name'])){
				echo json_encode(array('error' => 'Aucun nom n\'est passe.')); // pas de nom entré
		}elseif(empty($json['idFamily'])){
				echo json_encode(array('error' => 'Aucun id de famille n\'est passe.')); // pas d'idFamily entrée
		}else{
			$idFamily = $json['idFamily'];
			$nom = htmlspecialchars($json['name']);
			// Maintenant on chope les noms des parents de la famille, et on compare pour éviter le doublon.
			$sqlparent = "SELECT name FROM api_Parent WHERE Family_idFamily = ".$idFamily;
			$repparent = $this->select($sqlparent);
			// On rentre toutes les entrées de la famille dans un tableau.
			foreach ($repparent as $e) {
				$parents[] = strtolower($e['name']);
			}
			// on compare les noms.
			if(in_array(strtolower($nom), $parents)){
				echo json_encode(array('error' => 'Le compte de ce parent est deja enregistre.')); // parent déjà connu pour cette famille
			}else{
				// ici on a passé tous les filtres
				$sql = "INSERT INTO api_Parent (Family_idFamily, name) VALUES ('$idFamily', '$nom')";
				$rep = $this->insert($sql);

				$sqlget = "SELECT * FROM api_Parent ORDER BY idParent DESC LIMIT 1";
				$repget = $this->select($sqlget);
				echo json_encode($repget);
			}
		}
	}

	public function chore(){ // le json doit comprendre tous les champs, sinon on renvoit une erreur.
		$json = json_decode($_POST['json'], true);

		$idFamily = $json['Family_idFamily'];
		$name = htmlspecialchars($json['name'], ENT_QUOTES);
		$text = htmlspecialchars($json['text'], ENT_QUOTES);
		$xpToWin = $json['xpToWin'];
		$state = $json['state'];
		$imageName = $json['imageName'];
		$energy = $json['energy'];
		$validation = $json['validation'];
		$isRecurrent = $json['isRecurrent'];
		$date = $json['date'];

		$recMomentOfWeek = $json['recMomentOfWeek'];
		$lundi = $recMomentOfWeek[0];
		$mardi = $recMomentOfWeek[1];
		$mercredi = $recMomentOfWeek[2];
		$jeudi = $recMomentOfWeek[3];
		$vendredi = $recMomentOfWeek[4];
		$samedi = $recMomentOfWeek[5];
		$dimanche = $recMomentOfWeek[6];

		$recMomentOfDay = $json['recMomentOfDay'];
		$matin  = $recMomentOfDay[0];
		$dej = $recMomentOfDay[1];
		$gouter = $recMomentOfDay[2];
		$diner = $recMomentOfDay[3];

		if(!empty($json['childId'])){ 
			$childId = "";
			foreach ($json['childId'] as $child => $id) {
				$childId .= $id.", ";
			}
			$childId = substr($childId, 0, -2);
		}else{ $childId = ""; }

		$sql = "INSERT INTO api_ChoreRec (Family_idFamily, childId, xpToWin, name, text, state, imageName, energy, validation, date, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, matin, dejeuner, gouter, diner, isRecurrent) VALUES
('$idFamily', '$childId', '$xpToWin', '$name', '$text', '$state', '$imageName', '$energy', '$validation', '$date', '$lundi', '$mardi', '$mercredi', '$jeudi', '$vendredi', '$samedi', '$dimanche', '$matin', '$dej', '$gouter', '$diner', '$isRecurrent')";
		$rep = $this->insert($sql);

		$sqlget = "SELECT * FROM api_ChoreRec ORDER BY idChoreRec DESC LIMIT 1";
		$repget = $this->select($sqlget);
		echo json_encode($repget[0]);
	}

	public function choredone(){
		$json = json_decode($_POST['json'], true);

		if(!empty($json['ChoreRec_idChoreRec'])){ $ChoreRec_idChoreRec = $json['ChoreRec_idChoreRec']; }else{ $ChoreRec_idChoreRec = ""; }
		if(!empty($json['Children_idChildren'])){ $Children_idChildren = $json['Children_idChildren']; }else{ $Children_idChildren = ""; }
		if(!empty($json['isValidated'])){ $isValidated = $json['isValidated']; }else{ $isValidated = ""; }
		if(!empty($json['isCompleted'])){ $isCompleted = $json['isCompleted']; }else{ $isCompleted = ""; }
		if(!empty($json['momentOfWeek'] || $json['momentOfWeek'] == '0')){ $momentOfWeek = $json['momentOfWeek']; }else{ $momentOfWeek = ""; }
		if(!empty($json['momentOfDay'] || $json['momentOfDay'] == '0')){ $momentOfDay = $json['momentOfDay']; }else{ $momentOfDay = ""; }
		if(!empty($json['dueDate'])){ $dueDate = $json['dueDate']; }else{ $dueDate = ""; }
		if(!empty($json['timeCompleted'])){ $timeCompleted = $json['timeCompleted']; }else{ $timeCompleted = ""; }


		$sql = "INSERT INTO api_ChoreDone (ChoreRec_idChoreRec, Children_idChildren, isValidated, isCompleted, momentOfWeek, momentOfDay, dueDate, timeCompleted) VALUES ('$ChoreRec_idChoreRec', '$Children_idChildren', '$isValidated', '$isCompleted', '$momentOfWeek', '$momentOfDay', '$dueDate', '$timeCompleted')";
		$rep = $this->insert($sql);

		$sqlget = "SELECT * FROM api_ChoreDone ORDER BY idChoreDone DESC LIMIT 1";
		$repget = $this->select($sqlget);
		echo json_encode($repget[0]);
	}

	public function unlockedObject(){
		$json = json_decode($_POST['json'], true);

		$idChild = $json['Children_idChildren'];
		$idObject = $json['idObject']; // on rentre un id présent dans la liste des Objects (cf table ObjectList)

		$error = 0;
		if(empty($idObject)){
			echo json_encode(array('error' => 'Aucun id d\'objet ne passe.')); $error = 1;
		}else{
			// on vérifie que l'id existe.
			$error = 1;
		}
		if(empty($idChild)){
			echo json_encode(array('error' => 'Aucun id d\'enfant ne passe.')); $error = 1;
		}else{
			// on vérifie que l'id existe.
			$error = 1;
		}
		if($error != 1){
			$sql = "INSERT INTO api_ObjectUnlock (idObject, Children_idChildren) VALUES ($idObject, $idChild)";
			$req = $this->insert($sql);

			$sqlget = "SELECT * FROM api_ObjectUnlock ORDER BY idObjectUnlock DESC LIMIT 1";
			$repget = $this->select($sqlget);
			echo json_encode($repget[0]);
		}
	}

	public function settings(){
		$json = json_decode($_POST['json'], true);

		$validation = $json['validation'];
		$tempsdejeu = $json['tempsJeuMax'];
		$idChildren = $json['idChildren'];

		$sql = "INSERT INTO api_Settings (idChildren, validation, tempsJeuMax) VALUES ('$idChildren', '$validation', '$tempsdejeu')";
		$rep = $this->insert($sql);

		$sqlget = "SELECT * FROM api_Settings ORDER BY idSettings DESC LIMIT 1";
		$repget = $this->select($sqlget);
		echo json_encode($repget[0]);

	}

	public function support(){
		$json = json_decode($_POST['json'], true);

		$idFamily = $json['Family_idFamily'];
		$dToken = $json['deviceToken'];
		$os = $json['os'];

		$sql = "INSERT INTO api_Support (Family_idFamily, os, deviceToken) VALUES ($idFamily, '$os', '$dToken')";
		$rep = $this->insert($sql);

		$sqlget = "SELECT * FROM api_Support ORDER BY idSupport DESC LIMIT 1";
		$repget = $this->select($sqlget);
		echo json_encode($repget);
	}




/************* Fonctions tierses **/

	// Génération d'une chaine aléatoire
	function chaine_aleatoire($nb_car, $chaine = 'azertyuiopqsdfghjklmwxcvbn123456789')
	{
	    $nb_lettres = strlen($chaine) - 1;
	    $generation = '';
	    for($i=0; $i < $nb_car; $i++)
	    {
	        $pos = mt_rand(0, $nb_lettres);
	        $car = $chaine[$pos];
	        $generation .= $car;
	    }
	    return $generation;
	}

}

?>