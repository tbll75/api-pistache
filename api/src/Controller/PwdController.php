<?php 

namespace App\Controller;



class PwdController extends MailController{


	public function askPass(){ 
	
		//echo "HI";
	
		$hashmail = $_POST['hash'];

		// on récupere les mails
		$req = $this->select("SELECT mail, idFamily FROM api_Family");

		$mails = "";
		foreach ($req as $famille) {
			$mails[$famille['idFamily']] = hash_hmac('sha256', $famille['mail'], 'pistache', false);
		}

		// on compare dans la liste de la bdd
		if(in_array($hashmail, $mails)){
			$idFamily = array_search($hashmail, $mails);
			// On peut générer le mdp et tout ca.
			//echo $idFamily
			//echo $this->update("SELECT mail, idFamily FROM api_Family")
			
			//echo "</br>ID ".$idFamily;
			
			$pass = "apor14";
			$passCrypt = hash_hmac('sha256', "apor14", 'pistache', false);
			
			$this->update("UPDATE api_Family SET masterPassword='".$passCrypt."' WHERE idFamily = '".$idFamily."'");
			$rep = $this->select("SELECT mail FROM api_Family WHERE idFamily = '".$idFamily."'");
			
			foreach ($rep as $mail) {
				echo "</br>Nouveau mot de passe envoyé à ".$mail['mail']." !";
				$this->newPass($mail['mail'], $pass);
			}
			
		}else{
			echo '{"error":"Hash invalide"}';
		}
	}

}

 ?>