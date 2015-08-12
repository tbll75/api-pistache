<?php 

namespace App\Controller;



class FamilyController extends SQLController{

// getters ***************************************************************
	public function index(){ // /family
		echo json_encode($this->select("SELECT * FROM api_Family"));
	}

	public function indexfields($champs){ // /family/prenom,nom,maison
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_Family"));
	}

	public function show($id){ // /family/4
		echo json_encode($this->select("SELECT * FROM api_Family WHERE idFamily = ".$id));
	}

	public function showfields($id,$champs){ // /family/4/prenom,nom,jardin
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_Family WHERE idFamily = ".$id));
	}

	public function member($idFamily){
		if(empty($idFamily)){ die('il manque l\'id de la famille'); }
		$famille['parent'] = $this->select("SELECT * FROM api_Parent p WHERE p.Family_idFamily = ".$idFamily);
		$famille['children'] = $this->select("SELECT * FROM api_Children c WHERE c.Family_idFamily = ".$idFamily);
		echo json_encode($famille);
	}

	public function parent($idFamily){
		if(empty($idFamily)){ die('il manque l\'id de la famille'); }
		$famille['parent'] = $this->select("SELECT * FROM api_Parent p WHERE p.Family_idFamily = ".$idFamily);
		echo json_encode($famille);
	}

	public function children($idFamily){
		if(empty($idFamily)){ die('il manque l\'id de la famille'); }
		$famille['children'] = $this->select("SELECT * FROM api_Children c WHERE c.Family_idFamily = ".$idFamily);
		echo json_encode($famille);
	}

	public function alldata(){

		$json = $_POST['json'];
		$idFamily = $json['idFamily'];

		// Le but de cette methode est de retourner un object qui comprend toutes les infos data d'une famille.
		/*
		
		on va construire ce tableau :

		$famille = array(
			'parents' => array(
				array( "champ1" => "valeur1", "champ2" => "valeur2", "champ3" => "valeur3" ), //parent1
				array( "champ1" => "valeur1", "champ2" => "valeur2", "champ3" => "valeur3" )  //parent2
			),
			'enfants' => array(
				array( "champ1" => "valeur1", "champ2" => "valeur2", "champ3" => "valeur3", array(
					'ChoreRec' => array( /tous les rec de l'enfant/ ),
					'ChoreDone' => array( /tous les done/ ),
					'Settings' => array( /setting de l'enfant/ ),
					'Hero' => array( /tous sur le hero/ ),
					'ObjectUnlock' => array( /tous les ids des objets dévérouillés/ )
				)),
				array( "champ1" => "valeur1", "champ2" => "valeur2", "champ3" => "valeur3", array(
					'ChoreRec' => array( /tous les rec de l'enfant/ ),
					'ChoreDone' => array( /tous les done/ ),
					'Settings' => array( /setting de l'enfant/ ),
					'Hero' => array( /tous sur le hero/ ),
					'ObjectUnlock' => array( /tous les ids des objets dévérouillés/ )
				)),
				array( "champ1" => "valeur1", "champ2" => "valeur2", "champ3" => "valeur3", array(
					'ChoreRec' => array( /tous les rec de l'enfant/ ),
					'ChoreDone' => array( /tous les done/ ),
					'Settings' => array( /setting de l'enfant/ ),
					'Hero' => array( /tous sur le hero/ ),
					'ObjectUnlock' => array( /tous les ids des objets dévérouillés/ )
				))
			)
		);


		Pour construire un tel tableau ... on va pas tortiller du cul. on va faire une mega requete. Youhouuu !

		*/

		// Parents
		$sqlParent = "SELECT * FROM api_Parent WHERE Family_idFamily = ".$idFamily." ORDER BY idParent ASC";
		$repParent = $this->select($sqlParent);
		$family['parent'] = $repParent;

		// Enfants
		$sqlChild = "SELECT * FROM api_Children 
		WHERE Family_idFamily = $idFamily 
		ORDER BY Children.idChildren ASC";
		$repChild = $this->select($sqlChild);
		$family['children'] = $repChild;


		$i = 0;
		foreach ($family['children'] as $child) {
			$idChildren = $child['idChildren'];


			// ChoreRec 
			$sqlCR = "SELECT * FROM api_ChoreRec WHERE Children_idChildren = $idChildren";
			$repCR = $this->select($sqlCR);
			$family['children'][$i]['ChoreRec'] = $repCR;

			$for = $family['children'][$i]['ChoreRec'];
			if (is_array($for) || is_object($for)){
				foreach ($for as $chore) {
					$idChoreRec = $chore['idChoreRec'];

					// ChoreDone
					$sqlCD = "SELECT * FROM api_ChoreDone WHERE ChoreRec_idChoreRec = $idChoreRec";
					$repCD = $this->select($sqlCD);
					$family['children'][$i]['ChoreDone'] = $repCD;
				}
			}

			// Settings
			$sqlSettings = "SELECT * FROM api_Settings WHERE Children_idChildren = $idChildren";
			$repSettings = $this->select($sqlSettings);
			$family['children'][$i]['Settings'] = $repSettings[0];

			// Hero
			$sqlHero = "SELECT * FROM api_Hero WHERE Children_idChildren = $idChildren";
			$repHero = $this->select($sqlHero);
			$family['children'][$i]['Hero'] = $repHero[0];

			// ObjectUnlock
			$sqlOU = "SELECT GROUP_CONCAT(idObject) AS idObjects FROM api_ObjectUnlock WHERE Children_idChildren = $idChildren";
			$repOU = $this->select($sqlOU);
			$family['children'][$i]['ObjectUnlock'] = $repOU[0];

			$i++;

		}

		echo json_encode($family);
	}	


	public function askPass(){ 
		$hashmail = $_POST['hash'];
		echo $hashmail."<br/>Coucou<br/>";

		// on récupere les mails
		$req = $this->select("SELECT mail, idFamily FROM api_Family");

		$mails = "";
		foreach ($req as $famille) {
			$mails[$famille['idFamily']] = hash_hmac('sha256', $famille['mail'], 'secret', false);
		}

		// on compare dans la liste de la bdd
		if(in_array($hashmail, $mails)){
			// On peut générer le mdp et tout ca.
			echo 'oui';
			return true;
		}else{
			echo '{"error":"Hash invalide"}';
			return false;
		}
	}

}

 ?>