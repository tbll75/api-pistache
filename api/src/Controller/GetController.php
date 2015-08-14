<?php 

namespace App\Controller;

class GetController extends MailController{

	private $poubelle;


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
			$this->majorEntity['table'] = $entity;
		}

		$this->mainTraintment($data, $condition);

	}



	public function mainTraintment($data, $condition){

		// d'abord on devine de quelle table is s'agit en chequant l'id
		$table = $this->findTable($data);

		// On compare les colonne de notre data avec celle du SQL pour ne garder que le meilleur
		$fields = $this->compareDataSQL($data, $table);
		$champs = $fields[0];
		$tableaux = $fields[1];

		// on execute le requete pour les champs connus et on retourne la condition pour les recursifs
		// $futureCondition = $this->getLinesAndCondition($table, $champs, $condition);

		// On formate la condition

		// on envoit la boucle pour la recursivité

		// on retourne notre json de folie

		echo '<pre>';
		print_r($champs);
		print_r($tableaux);
		echo '</pre>';

	}



	public function getLinesForCondition($table, $champs, $condition){
		// on prépare les variables
		$champs = implode(', ', $champs);
		$condition = implode(' = ', $condition);

		// on fait la requete
		$rep = $this->select("SELECT $champs FROM $table WHERE $condition");
	}



	public function getTableStruct($table){

		// petite requete sql
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");

		// on filtre les infos intéressante
		$struct = array();
		foreach ($rep as $col) {
			$struct[] = $col['COLUMN_NAME'];
		}
		// on renvoit la réponse
		return $struct;

	}



	public function compareDataSQL($data, $table){

		// On chope la gueule de la table $table (les colonnes quoi)
		$struct = $this->getTableStruct($table);
		$champs = '';
		$tableaux = '';

		// On filtre pour trier les champs que l'on garde, les champs qui sont les entités 'enfants', et pour jeter le reste qui ne correspond a rien pour la BDD
		foreach ($data as $field) {
			// Si c'est un champ de la table SQL
			if(in_array($field, $struct))
				$champs[] = $field;
			elseif(is_array($field) && !empty($field))
				$tableaux[] = $field;
			else
				$this->poubelle[] = $table.":".$field; // on crés un champs poubelle qui regroupera tous les champs qui n'avaient aucune raison d'être là.
		}

		// On retourne les champs et les tableaux a fouiller pour la récursivité.
		return array($champs, $tableaux);
		
	}



	public function findTable($data){

		$table = "";
		// on cherche un champ commançant par 'id...'
		foreach ($data as $key => $value) {
			if(preg_match('#id[a-zA-Z]+#', $key)){
				$tableId = $key;
				break;
			}
		}

		// Si on ne trouve pas le champs id, on retourne une erreur
		if(empty($tableId)){
			echo "CAN NOT FIND TABLE";
			die();
		}else{
			$table = substr($tableId, 2);
			return $table;
		}
	}


}

?>