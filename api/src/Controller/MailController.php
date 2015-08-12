<?php 

namespace App\Controller;

class MailController extends SQLController{

	private $server = 'https://mandrillapp.com/api/1.0';
	private $mandrillKey = "FbUINsewlDpp_WAZV-a04w";


/**
	Fonctions de traitement : Welcome, newPass, hebdo 
**/
	public function nomdecettefonction($params1, $params2){ 
		/**
		 les params sont a définir dans l'index en GET si tu veux acceder a la fonction depuis l'extérieur (api).

		 Pour appeler la fonction lors d'une action avec la bdd (update ou create), il faut ajouter à l'endroit d'executiton :
		 	$this->nomdecettefonction($params1, $params2);
		 
		 Il faut aussi s'assurer que les héritages sont correctes.
		 Par défault les controllers héritent tous de SQLController. Si tu veux qu'un controller envoit un mail, change l'héritage de ce dernier à MailController.
		 Le multi-héritage n'existe pas en PHP.
		**/


		// parametres globaux du mail
		$title = "Titre";
		$from = "contact@pistache-app.com";
		$reply = "thibault@pistache-app.com";

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json"; // lien de l'api pour chercher les infos du template 
		$templateFields = array(
			"key" => $this->mandrillKey, 
			"name" => "slug" // Template Slug dans Mandrill->Outbound->Templates-> etle template
			);

		$result = $this->curlMail($api, $templateFields); // en gros c'est la requete pour choper les infos

		// récupération contenu Template
		if(!empty($result['publish_code'])) { $html = $result['publish_code']; }elseif(!empty($result['code'])) { $html = $result['code']; } 
		if(!empty($result['publish_text'])) { $text = $result['publish_text']; }elseif(!empty($result['text'])) { $text = $result['text']; }

		// Toutes les infos dynamique doivent se retrouver dans un tableau comme celui-ci.
		/**
		Pour effectuer une requete vers la bdd : 
			$req = $this->select("SELECT * FROM ma_table WHERE condition = 'ma_condition'");
		Changer 'select' en 'update', 'insert' ou 'delete', suivant le genre de la requete SQL.
		**/
		$mergeTo = array(
			array(
                "rcpt" => "mail.du@destinataire",
                "vars" => array(
                    array(
                        "name" => "NOMDEMAVAR", // nom de la variable sur le template (les variable sur le template s'écrive *|NOMDEMAVAR|*)
                        "content" => $valeurDeMaVar
                    ),
                    array( // S'il y a plusieurs var dans le template, on rajoute un autre array pour le même destinataire
                        "name" => "NOMDEMAVAR", // nom de la variable sur le template (les variable sur le template s'écrive *|NOMDEMAVAR2|*)
                        "content" => $valeurDeMaVar2
                    )
                )
            ),
			array( // S'il y a plusieur destinataire on rajoute un autre tableau pour le dernier
                "rcpt" => "mail.second@destinataire",
                "vars" => array(
                    array(
                        "name" => "NOMDEMAVAR", // nom de la variable sur le template (les variable sur le template s'écrive *|NOMDEMAVAR|*)
                        "content" => $valeurDeMaVarPourLeSecondDestinataire
                    ),
                    array( // S'il y a plusieurs var dans le template, on rajoute un autre array pour le même destinataire
                        "name" => "NOMDEMAVAR", // nom de la variable sur le template (les variable sur le template s'écrive *|NOMDEMAVAR2|*)
                        "content" => $valeurDeMaVar2PourLeSecondDestinataire
                    )
                )
            )
        );

		// on envoit à la fonction d'envoi les parametres.
		$result = $this->sendMessage($title, $html, $text, $from, $reply, $mergeTo);
	}
**/

	public function hebdomadaire(){
		
	}

/*
	public function askNewPass($mail){

		// parametres globaux du mail
		$title = "Demande de nouveau mot de passe Pistache";
		$from = "contact@pistache-app.com";
		$reply = "thibault@pistache-app.com";

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json"; // lien de l'api pour chercher les infos du template 
		$templateFields = array(
			"key" => $this->mandrillKey,
			"name" => "nouveau-mot-de-passe" // Template Slug dans Mandrill->Outbound->Templates-> etle template
			);

		$result = $this->curlMail($api, $templateFields); // en gros c'est la requete pour choper les infos

		// récupération contenu Template
		if(!empty($result['publish_code'])) { $html = $result['publish_code']; }elseif(!empty($result['code'])) { $html = $result['code']; } 
		if(!empty($result['publish_text'])) { $text = $result['publish_text']; }elseif(!empty($result['text'])) { $text = $result['text']; }

		// Génération du lien.
		$key = hash_hmac('sha256', $mail, 'secret', false);
		$link = $pistacheURL."/setting/parent?mdp=".$key;

		// Toutes les infos dynamique doivent se retrouver dans un tableau comme celui-ci.
		$mergeTo = array(
			array(
                "rcpt" => $mail,
                "vars" => array(
                    array(
                        "name" => "GENERATEDLINK", // nom de la variable sur le template (les variable sur le template s'écrive *|NOMDEMAVAR|*)
                        "content" => $link
                    )
                )
            )
        );

		// on envoit à la fonction d'envoi les parametres.
		$result = $this->sendMessage($title, $html, $text, $from, $reply, $mergeTo);
	}*/

	public function newPass($mail, $pass){

		// parametres globaux du mail
		$title = "Votre nouveau mot de passe Pistache";
		$from = "contact@pistache-app.com";
		$reply = "equipe@pistache-app.com";

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json"; // lien de l'api pour chercher les infos du template 
		$fields = array(
			"key" => $this->mandrillKey,
			"name" => "nouveau-mot-de-passe-2" // Template Slug dans Mandrill->Outbound->Templates-> etle template
			);

		$result = $this->curlMail($api, $fields); // en gros c'est la requete pour choper les infos

		// récupération contenu Template
		$html = '';
		$text = '';
		if(!empty($result['publish_code'])) { $html = $result['publish_code']; }elseif(!empty($result['code'])) { $html = $result['code']; } 
		if(!empty($result['publish_text'])) { $text = $result['publish_text']; }elseif(!empty($result['text'])) { $text = $result['text']; }

		// Toutes les infos dynamique doivent se retrouver dans un tableau comme celui-ci.
		$mergeTo = array(
			array(
                "rcpt" => $mail,
                "vars" => array(
                    array(
                        "name" => "PSWD",
                        "content" => $pass
                    )
                )
            )
        );

		// on envoit à la fonction d'envoi les parametres.
		$result = $this->sendMessage($title, $html, $text, $from, $reply, $mergeTo);
	}


		public function welcome($mail, $pass){

		// params mail
		$title = "Bienvenue chez Pistache";
		$from = "contact@pistache-app.com";
		$reply = "equipe@pistache-app.com";

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json"; // lien de l'api pour chercher les infos du template 
		$templateFields = array(
			"key" => $this->mandrillKey, 
			"name" => "bienvenue" // Template Slug dans Mandrill->Outbound->Templates-> etle template
			);

		$result = $this->curlMail($api, $templateFields); // en gros c'est la requete pour choper les infos

		// récupération contenu Template
		if(!empty($result['publish_code'])) { $html = $result['publish_code']; }elseif(!empty($result['code'])) { $html = $result['code']; } 
		if(!empty($result['publish_text'])) { $text = $result['publish_text']; }elseif(!empty($result['text'])) { $text = $result['text']; }

		// prepare
		$mergeTo = array(
			array(
                "rcpt" => $mail,
                "vars" => array(
                    array(
                        "name" => "PSWD",
                        "content" => $pass
                    )
                )
            )
        );

		$result = $this->sendMessage($title, $html, $text, $from, $reply, $mergeTo);

	}

/**
	Fonctions d'envoi
**/
	public function sendMessage($title, $html, $text, $from, $reply, $mergeTo){
		// api Mandrill à envoyer
		$api = '/messages/send.json';
		// traitement un pour l'envoi
		$to = '';
		foreach ($mergeTo as $user) {
			if(!empty($user['rcptName']))
				$to[] = array("type" => "to", "email" => $user['rcpt'], "name" => $user['rcptName']);
			else
				$to[] = array("type" => "to", "email" => $user['rcpt']);
		}
		// Content
		$fields = array(
			"key" => $this->mandrillKey, 
		    "message" => array(
		        "html" => $html,
		        "text" => $text,
		        "subject" => $title,
		        "from_email" => $from,
		        "from_name" => "Pistache",
		        "to" => $to,
		        "headers" => array(
		            "Reply-To" => $reply
		        ),
		        "merge_vars" => $mergeTo,
		        "important" => false,
		        "track_opens" => null,
		        "track_clicks" => null,
		        "auto_text" => null,
		        "auto_html" => null,
		        "inline_css" => null,
		        "url_strip_qs" => null,
		        "preserve_recipients" => null,
		        "view_content_link" => null,
		        // "bcc_address" => "message.bcc_address@example.com", // mail pour la copy caché des mails.
		        "tracking_domain" => null,
		        "signing_domain" => null,
		        "return_path_domain" => null/*,
		        "google_analytics_domains" => array(
		            "example.com"
		        ),
		        "google_analytics_campaign" => "message.from_email@example.com",
		        "metadata" => array(
		            "website" => "www.example.com"
		        )*/
		    ),
		    "async" => false
		);

		// envoie
		return $this->curlMail($api, $fields);
	}

	public function curlMail($api, $fields){

		$curl = curl_init();
		curl_setopt( $curl,CURLOPT_URL, $this->server.$api );
		curl_setopt( $curl,CURLOPT_POST, true );
		curl_setopt( $curl,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl,CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = json_decode(curl_exec($curl), true);
		curl_close($curl);

		return $result;

	}

}

 ?>