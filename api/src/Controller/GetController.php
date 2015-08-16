<?php 

namespace App\Controller;

class GetController extends MailController{

	private $poubelle;
	private $callBack = '';

	private $idError = 0;
	private $switcher = array(
				"FamilyData" => "api_Family",
				"FamilyMember" => "api_Children",
				"Chore" => "api_ChoreRec",
				"ChoreChild" => "api_ChoreDone", 
				"Settings" => "api_Settings",
				"hero" => "api_Hero",
				// "listeDebloque" => "api_ObjectUnlock"
			);


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		// Si le tableau n'existe pas
		if(!isset($this->switcher[$entity])){
			$this->ids[] =  '"error'.$this->idError++.'":"Entity '.$entity.' unknown"';
			return false;
		}else{
			$entity = $this->switcher[$entity];
		}

		// On 'nettoie' la data, ou alors on cré la structure de la bdd par une jolie requete de folie.
		/**

		*/
		$this->getAllStructure($entity);

		echo '<br/>************************************************************************************************<br/>';

		// on définie les conditions
		$infos = $this->findTable($data);
		$condition = array($infos[1], $infos[2]);


		$this->mainTraintment($data, $condition);

		$this->callBack = substr($this->callBack, 0, -1);
		echo $this->callBack;

	}



	public function mainTraintment($data, $condition){


		echo "<br/>----------------------------<br/>DATA : <pre>";
		print_r($data);
		echo "</pre>";

		// d'abord on devine de quelle table is s'agit en chequant l'id
		$infos = $this->findTable($data);
		if(!$infos){ return false; }
		$table = $infos[0];

		// On compare les colonne de notre data avec celle du SQL pour ne garder que le meilleur
		$fields = $this->compareDataSQL($data, $table, $condition[0]);
		if(!$fields){ return false; }
		$champs = $fields[0];
		$tableaux = $fields[1];
		echo "<br/>----------------------------<br/>CHAMPS ET TABLEAUX : <pre>";
		print_r($champs);
		print_r($tableaux);
		echo "</pre>";


		// on execute le requete pour les champs connus et on retourne la condition pour les recursifs
		$infos = $this->getLinesAndNewCondition($table, $champs, $condition);
		$condition = $infos[0];
		$ids = $infos[1];

		// on envoit la boucle pour la recursivité
		if(!empty($ids) && !empty($tableaux)){
			foreach ($ids as $id) {
				echo "<br/>----------------------------<br/>ID DATA : ".$id;
				$condition[1] = $id;
				echo "<br/>";

				foreach ($tableaux as $tableau) {
					$nb = count($data[$tableau]);
					echo $this->callBack .= ',"'.$tableau.'":<br/>';
					// for ($i=0; $i < $nb; $i++) { 
						echo "<br/>----------------------------------------------------------------------------------------------------------------<br/>NEW : ".$tableau." : ";
						$this->mainTraintment($data[$tableau], $condition);
					// }

				}

			}
		}

		echo $this->callBack .= '}<br/>';

		// on retourne notre json de folie
		return true;

	}



	public function getAllStructure($entity){

	}



	public function getLinesAndNewCondition($table, $champs, $condition){
		// on prépare les variables
		$champs = implode(', ', $champs);
		$whereClaused = implode(' = ', $condition);

		// on fait la requete
		$rep = $this->select("SELECT $champs FROM $table WHERE $whereClaused");
		echo "<br/>----------------------------<br/>REP : ".$table." -> ".$whereClaused."<pre>";
		print_r($rep);
		echo "</pre>";


		// let's start le CallBack
		if(count($rep) > 1)
			echo $this->callBack .= '[<br/>';

		foreach ($rep as $line) {
			echo $this->callBack .= '{<br/>';
			foreach ($line as $key => $value) {
				echo $this->callBack .= '"'.$key.'":"'.$value.'",<br/>';
			}
			$this->callBack = substr($this->callBack, 0, -1);
			echo $this->callBack .= '}<br/>';
		}
		$this->callBack = substr($this->callBack, 0, -1);

		if(count($rep) > 1)
			echo $this->callBack .= ']<br/>';

		// on doit retourner un tableau avec les ids du select.
		$ids = '';
		foreach ($rep as $line) {
			$ids[] = $line['id'.substr($table, 4)];
		}

		echo "<br/>----------------------------<br/>IDS<pre>";
		print_r($ids);
		echo "</pre>";

		// on construit la nouvelle condition
		$condition = array(substr($table, 4)."_id".substr($table, 4), $condition[1]);
		echo "<br/>----------------------------<br/>CONDITION : <pre>";
		print_r($condition);
		echo "</pre>";

		return array($condition, $ids);
	}



	public function getTableStruct($table, $parentColumn){

		// petite requete sql
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");

		// on filtre les infos intéressante
		$struct = '';
		foreach ($rep as $col) {
			$struct[] = $col['COLUMN_NAME'];
		}
		// on renvoit la réponse
		if(!in_array($parentColumn, $struct)){
			echo '<br/>PAS DE CLE PARENT TROUVE !!!';
			return false;
		}else
			return $struct;

	}



	public function compareDataSQL($data, $table, $parentColumn){

		// On chope la gueule de la table $table (les colonnes quoi)
		$struct = $this->getTableStruct($table, $parentColumn);
		if(!$struct){ return false; }
		$champs = '';
		$tableaux = '';

		// On filtre pour trier les champs que l'on garde, les champs qui sont les entités 'enfants', et pour jeter le reste qui ne correspond a rien pour la BDD
		foreach ($data as $field => $value) {
			// Si c'est un champ de la table SQL
			if(in_array($field, $struct))
				$champs[] = $field;
			elseif(is_array($value) && !empty($value))
				$tableaux[] = $field;
			else
				$this->poubelle[] = $table.":".$field; // on crés un champs poubelle qui regroupera tous les champs qui n'avaient aucune raison d'être là.
		}

		// On retourne les champs et les tableaux a fouiller pour la récursivité.
		return array($champs, $tableaux);
		
	}



	public function findTable($data){

		$table = "";
		$idKey = "";
		$idValue = "";
		// on cherche un champ commançant par 'id...'
		foreach ($data as $key => $value) {
			if(preg_match('/^id[a-zA-Z]+/', $key)){
				$idKey = $key;
				$idValue = $value;
				break;
			}
		}

		// Si on ne trouve pas le champs id, on retourne une erreur
		if(empty($idKey)){
			echo "CAN NOT FIND TABLE";
			return false;
		}else{
			$table = "api_".substr($idKey, 2);
			echo "TABLE : ".$table;
			return array($table, $idKey, $idValue);
		}
	}


}

?>