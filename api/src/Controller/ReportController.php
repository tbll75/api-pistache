<?php 

namespace App\Controller;

class ReportController extends MailController{

public function selectRec($momentOfWeek){
		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$diffDay = date('N')-$momentOfWeek;
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
		$rep = $this->select("SELECT * FROM api_ChoreRec WHERE isRecurrent = 0 /*AND date = $day*/ AND isActive = 1"); // FAIRE LA DATE ****************************** //
		// on place les infos dont on a besoin
		$punctualRec = '';
		foreach ($rep as $choreRec) {
			$children = explode(', ', $choreRec['childId']);
			foreach ($children as $child) {
				$punctualRec[] = array("idChild" => $child, "idChoreRec" => $choreRec['idChoreRec'], "today" => $day, "day" => $momentOfWeek, "moment" => "4"); // 4 -> toute la journée
			}
		}

		return array_merge($recurrentRec, $punctualRec);
	}

	public function selectDone($momentOfWeek){

		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$diffDay = date('N')-$momentOfWeek;
		$day = strtotime(date('d-m-Y')) - $diffDay*60*60*24;
		$dayBefore = $day - 60*60*24;
		$dayAfter = $day + 60*60*24;
		// select recurrent DONE
		$rep = $this->select("SELECT * FROM api_ChoreDone WHERE momentOfWeek = '$momentOfWeek' /*AND dueDate > $day AND dueDate < $dayBefore */");
		// generate chore Done of the day
		$done = '';
		foreach ($rep as $choreDone){
			// onprend que ceux d'ajd.
			preg_match('!\d+!', $choreDone['dueDate'], $choreTimeStamp);
			$choreTimeStamp =  substr($choreTimeStamp[0], 0, -3);
			//  si ajd
			if($choreTimeStamp > $day && $choreTimeStamp < $dayAfter)
				$done[] = array("idChild" => $choreDone['Children_idChildren'], "idChoreRec" => $choreDone['ChoreRec_idChoreRec'], "today" => $day, "day" => $choreDone['momentOfWeek'], "moment" => $choreDone['momentOfDay']/*, "done" => 1*/); 
		}

		return $done;

	}






	public function todayReport(){
		// today		
		$momentOfWeek = date('N') - 1;
		$today = strtotime(date('d-m-Y'));
		$yesterday = $today - 60*60*24;
		$tomorrow = $today + 60*60*24;

		$rec = $this->selectRec($momentOfWeek);
		$done = $this->selectDone($me);

		$report = $this->sortChore($rec, $done);

		$dailyReport = "(".implode('), (', $report).")";
		// On insert dans la bdd
		$this->insert("INSERT INTO api_DailyReport (idChild, idChoreRec, today, day, moment, done) VALUES $dailyReport");
	}



	public function checkYesterdayReport(){
		// today		
		$momentOfWeek = date('N') - 2; // of yesterday
		$jour = array("0" => "lundi", "1" => "mardi", "2" => "mercredi", "3" => "jeudi", "5" => "vendredi", "5" => "samedi", "6" => "dimanche");
		$today = strtotime(date('d-m-Y'));
		$yesterday = $today - 60*60*24;
		$tomorrow = $today + 60*60*24;

		$rep = $this->select("SELECT idChild, idChoreRec, today, day, moment FROM api_DailyReport WHERE today = $yesterday AND done = 0");
		foreach ($rep as $choreMissed) {
			echo '<pre>';
			print_r($choreMissed);
			echo '</pre>';
		}

		$done = $this->selectDone($momentOfWeek);

		echo "------";

		echo '<pre>';
		print_r($done);
		echo '</pre>';
	}



	public function dailyReport(){
		$this->todayReport();
		$this->checkYesterdayReport();
	}










	function sortChore($rec, $done) {
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
	    		$result[] = implode(', ',$choreRec);
	    	}
		}
		return $result;
	}

}

?>