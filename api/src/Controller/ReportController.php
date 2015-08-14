<?php 

namespace App\Controller;

class ReportController extends MailController{



/**
Traintement pour mettre dans la bdd chaque jour ce qu'il faut.
*/

	public function dailyReport(){
		$this->checkYesterdayReport();
		$this->todayReport();
	}



	public function selectRec($momentOfWeek){
		// LISTE DES TACHE QUI DEVRAIT ETRE FAITES A CE JOUR
		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$diffDay = (date('N') - 1) -$momentOfWeek;
		$day = strtotime(date('d-m-Y')) - $diffDay*60*60*24;
		// select recurrent REC
		$rep = $this->select("SELECT * FROM api_ChoreRec WHERE isRecurrent = 1 AND $jour[$momentOfWeek] = 1 AND isActive = 1");
		// generate chore of the day
		$recurrentRec = '';
		foreach ($rep as $choreRec){
			// quels enfants ?
			$children = explode(', ', $choreRec['childId']);
			foreach ($children as $child) {
				// pour chaque enfant on rentre la tache
				if($choreRec['matin'] == 1)
					$recurrentRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "0");
				if($choreRec['dejeuner'] == 1)
					$recurrentRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "1");
				if($choreRec['gouter'] == 1)
					$recurrentRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "2");
				if($choreRec['diner'] == 1)
					$recurrentRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "3");
			}
		}

		// select punctual REC
		$rep = $this->select("SELECT * FROM api_ChoreRec WHERE isRecurrent = 0 AND date = '$day' AND isActive = 1");
		// on place les infos dont on a besoin
		$punctualRec = array();
		foreach ($rep as $choreRec) {
			$children = explode(', ', $choreRec['childId']);
			foreach ($children as $child) {
				$punctualRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "4"); // 4 -> toute la journée
			}
		}

		$arrayRecToDo = array_merge($recurrentRec, $punctualRec);

		return $arrayRecToDo;
	}

	public function selectDone($momentOfWeek){
		// LISTE DES TACHES EFFECTIVEMENT FAITES A CE JOUR
		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$diffDay = (date('N') - 1) - $momentOfWeek;
		$day = strtotime(date('d-m-Y')) - $diffDay*60*60*24;
		$dayBefore = $day - 60*60*24;
		$dayAfter = $day + 60*60*24;
		// select recurrent DONE
		$rep = $this->select("SELECT * FROM api_ChoreDone WHERE momentOfWeek = '$momentOfWeek' AND dueDate > '$day' AND dueDate < '$dayAfter'");
		// generate chore Done of the day
		$done = '';
		foreach ($rep as $choreDone){
			$done[] = array("idChild" => $choreDone['Children_idChildren'], "idChoreRec" => $choreDone['ChoreRec_idChoreRec'], "today" => $day, "day" => $choreDone['momentOfWeek'], "moment" => $choreDone['momentOfDay']/*, "done" => 1*/); 
		}

		return $done;

	}






	public function todayReport(){
		// INSERT LES TACHES DU JOUR
		// today		
		$momentOfWeek = date('N') - 1;
		$today = strtotime(date('d-m-Y'));
		$yesterday = $today - 60*60*24;
		$tomorrow = $today + 60*60*24;

		$rec = $this->selectRec($momentOfWeek);
		$done = $this->selectDone($momentOfWeek);
		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo 'TACHES DU JOUR';
		echo '<pre>';
		print_r($rec);
		echo '</pre>';
		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo 'TACHES VALIDES DU JOUR';
		echo '<pre>';
		print_r($done);
		echo '</pre>';

		$report = $this->sortChore($rec, $done, true);

		$dailyReport = "(".implode('), (', $report).")";
		// On insert dans la bdd
		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo 'REPORT DU JOUR (insert)';
		echo '<pre>';
		print_r($report);
		echo '</pre>';
		$this->insert("INSERT INTO api_DailyReport (idChild, idChoreRec, today, day, moment, done) VALUES $dailyReport");
	}



	public function checkYesterdayReport(){
		// VERIFIE SI LES TACHES NON FAITES D'HIER SONT FAITES AUJOURD'HUI
		// today		
		$momentOfWeek = date('N') - 2; // of yesterday
		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$today = strtotime(date('d-m-Y'));
		$yesterday = $today - 60*60*24;
		$tomorrow = $today + 60*60*24;

		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo "REPORT D'HIER (RATE)";
		// On récupere les taches non faites d'hier
		$rep = $this->select("SELECT idChild, idChoreRec, today, day, moment FROM api_DailyReport WHERE today = $yesterday AND done = 0");
		foreach ($rep as $missed) {
			$choreMissed[] = $missed;
		}
		echo '<pre>';
		print_r($choreMissed);
		echo '</pre>';

		// On prend les taches validé aujourdhui
		$done = $this->selectDone($momentOfWeek);

		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo "TACHE D'HIER FAITE AUJOURD'HUI";
		echo '<pre>';
		print_r($done);
		echo '</pre>';

		echo "----------------------------------------------------------------------------------------------------<br/>";
		echo "UP REPORT HIER (update)";
		$report = $this->sortChore($choreMissed, $done, false);
		echo '<pre>';
		print_r($report);
		echo '</pre>';
		if(empty($report)){ $report = array(); }
		foreach ($report as $toUp) {
			$toUp = explode(', ', $toUp);
			$str = "idChild = ".$toUp[0]." AND idChoreRec = ".$toUp[1]." AND today = ".$toUp[2]." AND day = ".$toUp[3]." AND moment = ".$toUp[4];
			$this->update("UPDATE api_DailyReport SET done = 1 WHERE $str");
		}
		
	}



	function sortChore($rec = array(), $done = array(), $all) {
		// CONSTRUIT LE TABLEAU AVEC LE PARAMETRE done A JOUR POUR CHAQUE TACHE
		$result = NULL;
		// pour chaque tache Rec
		foreach($rec as $choreRec) {
			$isIn = 0;
			// pour chaque tache faite
    		foreach ($done as $choreDone) {
    			// on vérifie que la tache est bien faite.
    			if($choreRec == $choreDone){
    				$isIn = 1;
    				break;
    			}
	    	}
	    	// si elle a effectivement été faite
	    	if($isIn == 1){
	    		$choreRec['done'] = 1;
	    		$result[] = implode(', ',$choreRec);
	    	// sinon..
	    	}elseif($isIn == 0){
	    		$choreRec['done'] = 0;
	    		if($all)
	    			$result[] = implode(', ',$choreRec);
	    	}
		}
		return $result;
	}



/**
Traitement de la data pour le reporting sous X jours.
*/


	public function report($daysBack){

		// on définie les date qui cernes la/les zone/s de reporting/infotainment
		echo $endTime = time();
		echo "<br>". $startTime = $endTime - $daysBack * 60 * 60 * 24;
		// période d'avant.
		$endTimeBackPeriode = $startTime - 60 * 60 * 60 * 24;
		$startTimeBackPeriode = $endTimeBackPeriode - $daysBack * 60 * 60 * 24;
		// array contenant les deux périodes et leur tableau de sortie.
		$periodes = array(
			0 => array(
				"endTime" => $endTime,
				"startTime" => $startTime,
				"child" => null
				),
			1 => array(
				"endTime" => $endTimeBackPeriode,
				"startTime" => $startTimeBackPeriode,
				"child" => null
				)
			);

		foreach($periodes as $periode){
			// sql pour choper toute la data qui nous interesse
			$rep = $this->select("SELECT * FROM api_DailyReport WHERE today < '".$periode['endTime']."' AND today > '".$periode['startTime']."'");
			// On construit un tableau structurer : enfant > chore::done 0/1/2
			$periode['child'] = array();
			foreach ($rep as $report){

				// on fait un tableau avec les 'done' à 0,1 et 2
				if($report['done'] == 0){
					$periode['child'][0][] = $report;
				}elseif($report['done'] == 1){
					$periode['child'][1][] = $report;
				}elseif($report['done'] == 1){
					$periode['child'][2][] = $report;
				}
			}
		}
		$childTab = $periodes[0]['child'];
		$childTabBackPeriode = $periodes[1]['child'];

		// A ce niveau chaque enfant possède des données statistique sur la période en cours choisie. On va en rajouter (rapport par rapport à la période précédante etc..)
		echo "--------------------------<br/>Periode en cours";
		echo "<pre>";
		print_r($childTab);
		echo "</pre>";
		echo "--------------------------<br/>Periode précédante";
		echo "<pre>";
		print_r($childTabBackPeriode);
		echo "</pre>";

	}

























}

?>