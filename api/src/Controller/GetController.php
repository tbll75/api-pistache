<?php 

namespace App\Controller;

class GetController extends MailController{

	private $idParentReference;
	private $idError = 0;
	private $majorEntity;
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
			$this->majorEntity['table'] = $entity;
		}


		$dataToShow = $this->mainTraitment($entity, $data, $integratedDependences);

		// traitement data a montrer
		echo "<br/>---------------------------------------------------------------------------------------------------------------------------------<br/>";
		print_r($dataToShow);
	}



	public function mainTraitment($entity, $data, $integratedDependences){

		// On récupère la structure de la table ciblée.
		$tableStruct = $this->getTableStruct($entity);

		// On compare avec la data pour filtrer les champs des 'dataEnfants'.
		$sortedData = $this->filterDataForThisTable($entity, $data, $tableStruct);

		// on commence l'output
		$output = '{"'.array_search($entity, $this->switcher).'":';
		// On cherche les champs souhaité
		if(empty($condition)){ $condition = $this->majorEntity['key']." = '".$this->majorEntity['value']."'"; }
		$output .= $this->selectTableElements($entity, $sortedData[0], $condition); // [0] pour les champs de la bdd

		echo 'TROISIEME';

		$output .= '}';
		echo '<pre>';
		print_r($output);
		echo '</pre>';

		die();


	}



	public function selectTableElements($table, $fields, $condition){
		// on préprare
		$fields = implode(', ', $fields);
		$str = '';
		// on select
		$rep = $this->select("SELECT $fields FROM $table WHERE $condition");

			echo '<pre>';
			print_r($rep);
			echo '</pre>';
		// s'il y a plusieurs réponses
		if(count($rep) > 1)
			$str .= '[';
		// on parcours le ligne
		foreach ($rep as $entry) {
			$str .= '{';

			echo 'PREMIER<pre>';
			print_r($entry);
			echo '</pre>';

			// on parcours les key=>value
			foreach ($entry as $key => $value) {

				echo 'DEUXIEME<pre>';
				print_r($key.' - '.$value);
				echo '</pre>';

				echo $str .= '"'.$key.'":"'.$value.'", ';
				echo "<br/>";
			}

			// echo $str .= substr($str, 0, -2).'}, ';
			// echo "<br/>";
		}
		echo $str .= substr($str, 0, -2);
		if(count($rep) > 1)
			$str .= '}';

		return $str;
	}



	public function filterDataForThisTable($table, $data, $tableStruct){
		// on compare si oui on garde sinon ou met de coté pour plus tard
		$fields = '';
		$otherTable = '';
		foreach ($data as $key => $value) {
			// c'est dans la structure
			if(in_array($key, $tableStruct))
				$fields[] = $key;
			elseif (is_array($value)) 
				$otherTable[] = $key;

			// par aillerus on stock l'id du patron ci nécéssaire
			if($key == $this->majorEntity['key']){
				$this->majorEntity['value'] = $value;
				unset($fields[array_search($key, $fields)]);
			}
		}

		return array($fields, $otherTable);

	}



	public function getTableStruct($table){
		// petite requete sql
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		// on filtre les infos intéressante
		$struct = array();
		foreach ($rep as $col) {
			$struct[] = $col['COLUMN_NAME'];
			// si c'est le patron de la requete, on sauvegarde son identificateur.
			if($col['ORDINAL_POSITION'] == 1 && $this->majorEntity['table'] == $table)
				$this->majorEntity['key'] = $col['COLUMN_NAME'];
		}
		// on renvoit la réponse
		return $struct;
	}




}
