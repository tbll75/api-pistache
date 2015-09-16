<?php 

namespace App\Controller;

class PartnerController extends SQLController{

	public function familychildren($field = null){
		if($field == null){ $error = array('error' => 400, 'msg' => 'il manque l\'id ou le mail de la famille.'); echo json_encode($error); die(); }
		// On prépare l'erreur
		$error = array('error' => 400, 'msg' => 'il manque l\'id ou le mail de la famille.');
		// Si erreur il y a :
		if(filter_var($field, FILTER_VALIDATE_EMAIL)){
			// Le client a passé un mail
			$condition = "(";
			$condition .= "SELECT idFamily FROM api_Family WHERE mail = '$field' LIMIT 1";
			$condition .= ")";
		}
		else
		{
			$condition = "(";
			$condition .= "SELECT Family_idFamily FROM api_Support WHERE uniqueDeviceId = '$field' LIMIT 1";
			$condition .= ")";
		}
		/*
		else if(preg_match('[\d]', $field)){
			// Le client a passé un id
			$condition = $field;
		}
		else{ echo json_encode($error); die();  }
		*/
		// Cb d'enfant en tout ?
		$sqlnb = "SELECT COUNT(idChildren) as nbChildren FROM api_Children WHERE Family_idFamily = ".$condition;
		$famille['nb'] = $this->select($sqlnb)[0]['nbChildren'];
		// Qui sont-ils ?
		if($famille['nb'] > 0){
		
			$sql = "SELECT idChildren, name, photo, level, xp, energy, nbTicksPlay FROM api_Children WHERE Family_idFamily = ".$condition;
			$result = $this->select($sql);
			$famille['children'] = $result;

			for ($i=0 ; $i<count($famille['children']) ; $i++)
			{
				$sqlMax = "SELECT MaxPlayTime FROM api_Settings WHERE Children_idChildren = ".$famille['children'][$i]['idChildren'];
				$maxPlayTime = $this->select($sqlMax)[0]['MaxPlayTime'];
				
				if ($famille['children'][$i]['nbTicksPlay'] > $maxPlayTime)
				{
					$famille['children'][$i]['isAbleToPlay'] = 2;
				}
				else if ($famille['children'][$i]['energy'] < 50)
				{
					$famille['children'][$i]['isAbleToPlay'] = 3;
				}
				else
				{
					$famille['children'][$i]['isAbleToPlay'] = 1;
				}
				
				// remove unecessary variables
				unset($famille['children'][$i]['nbTicksPlay']);
				unset($famille['children'][$i]['energy']);
			}
			
			
			for ($i=0 ; $i<count($famille['children']) ; $i++)
			{
				$sqlHero = "SELECT * FROM api_Hero WHERE Children_idChildren = ".$famille['children'][$i]['idChildren'];
				$famille['children'][$i]['hero'] = $this->select($sqlHero)[0];
			}
		
		}
			
		
		// On retourne l'objet json.
		echo json_encode($famille);

	}

	public function isAbleToPlay($idChild){
		//
		$sql = "SELECT energy FROM api_Children WHERE idChildren = ".$idChild;
		$rep = $this->select($sql);
		// On analyse la reponse
		if($rep[0]['energy'] >= 70){
			// IsAbleToPlay
			echo 'true';
			return true;
		}else{
			// NotAllowed
			echo 'false';
			return false;

		}

	}

	public function gainXp($idChild, $gain){
		// Si le gain est trop grand, on tronque :)
		if($gain > 50){ $gain = 50; }

		// choper le lvl de l'enfant
		$sqlLvl = "SELECT level, xp FROM api_Children WHERE idChildren = ".$idChild;
		$rep = $this->select($sqlLvl);
		$level = $rep[0]['level'];
		$xp = $rep[0]['xp'];

		// On calcul l'xp max du niveau
		$xpNeeded = pow(2, $level -1);
		$xp += $gain;

		// doit-on passer au niveau superieur ?
		if( $xp >= $xpNeeded){
			$xp -= $xpNeeded;
			$level++; 
		}

		// On insert les nouvelles valeurs
		$sqlInsert = "UPDATE api_Children SET level = '$level', xp = '$xp' WHERE idChildren = ".$idChild;
		$reqInsert = $this->insert($sqlInsert);
		// On sort les news
		$sqlSelect = "SELECT * FROM api_Children WHERE idChildren = ".$idChild;
		$reqSelect = $this->select($sqlSelect);
		// le petit echo qui va bien ^
		echo json_encode($reqSelect[0]);
	}
	
	public function addticks($idChild){
		
		$sqlInsert = "UPDATE api_Children SET nbTicksPlay = nbTicksPlay +1 WHERE idChildren = ".$idChild;
		$reqInsert = $this->update($sqlInsert);

		// le petit echo qui va bien ^
		echo json_encode(true);
	}
	
	public function eraseticks(){
		
		$sqlInsert = "UPDATE api_Children SET nbTicksPlay = 0";
		$reqInsert = $this->update($sqlInsert);

		// le petit echo qui va bien ^
		echo json_encode(true);
	}
}