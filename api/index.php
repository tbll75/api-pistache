<?php 

	ini_set('display_errors', 1);

	require 'vendor/autoload.php';


	$router = new App\Router\Router($_GET['url']);


	$router->get('/', "Accueil#index"); // page qui ne sert à rien ;)

	$router->get('/connect', "Connect#index"); // par défault on retourn "Mouhahahaa t'as cru quoi toi !". Ou alors on dit juste "bonjour".
	$router->post('/connect/:id', "Connect#firststep")->with('id','[0-9]+'); // on chope un id et on retourne le token_key
	$router->post('/connect/:token_key-:pass', "Connect#secondstep")->with('token_key',"[a-zA-Z0-9]+")->with('pass',"[a-zA-Z0-9]+"); 	// on vérifie que le token_key existe, 
																												// on dé-hash le mdp et on le re-hash avec la clé serveur
																												// si aucune erreur, on retourne true
																												// ET on revoie la clé d'iD tout en mettant la date à 0.
// Le token_key est en fait la clé de hash coté utilisateur, mais générée coté serveur. Ainsi le mdp n'apparait jamais en clair.
// On envoit donc la token_key fraichement générée et rangée dans un tableau 'token_key_table', et une fois qu'il est utilisé, on le supprime (la table doit donc etre vide la plupart du tps)
// lorsque le serveur a vérifié que le mdp est correct, le token_key devient la clé d'iD de la famille (et non plus son id qui elle etant fixe est reperable).
// A partir de maintenant, dès que la connection est établie : chaque objet JSON doit avoir en premier parametre la clé d'iD dans le champ 'KEY'.


// POUR TOUTE LA SUITE ON SUPPOSE LA CONNEXION ETABLIE, ET ON SUPPOSE QUE LA VERIFICATION SE FERA PAR L'APPEL D'UNE FONCTION GLOBALE.

// Trois différentes requetes 
/*
	
	SELECT : $router->get(...);		// avec des parametre url pour filtrer par champs par exemple
	INSERT : $router->post(/create/...);
	UPDATE : $router->post(/update/...);

	*/

// Ordre/Menu
/*	
	INSERTs (creations)
	UPDATEs (mise a jour)
	SELECTs (getters)
	NOTIFs (gestions particulière des notifs) // on ne peut ni en crée ni en mettre a jour avec la bdd (on devrait tu crois ?), en tout cas on peu en executer.
*/

/**** Fin Mode Emploi ****/

// (Je vais mettre les procédure comme elles viennent en ouverture de jeu)

// INSERT
	$router->post('/create/family', 'Create#family');//->with('name', '[a-zA-Z]+')->with('mail', '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$'); // rentre dans la bdd (si mail non existant) et retourne id.
	$router->post('/create/child', 'Create#child');
	$router->post('/create/parent', 'Create#parent');
	$router->post('/create/settings', 'Create#settings');
	$router->post('/create/chore', 'Create#chore');
	$router->post('/create/choredone', 'Create#choredone');
	$router->post('/create/unlockobject', 'Create#unlockobject');
	$router->post('/create/support', 'Create#support');

// UPDATE
	$router->post('/update/family', 'Update#family'); 
	$router->post('/update/child', 'Update#child'); 
	$router->post('/update/parent', 'Update#parent');
	$router->post('/update/settings', 'Update#settings');
	$router->post('/update/chore', 'Update#chore');
	$router->post('/update/choredone', 'Update#choredone');
	$router->post('/update/unlockobject', 'Update#unlockobject');
	$router->post('/update/alldata', 'Update#alldata');
	$router->post('/update/password', 'Update#pass');

// DELETE
	$router->post('/delete', 'Delete#entity');



// GETTERS
// Getters Family ********************************************************
	$router->get('/family/:idFamily/member', "Family#member")->with('idFamily', '[0-9]+');
	$router->get('/family/:idFamily/parent', "Family#parent")->with('idFamily', '[0-9]+');
	$router->get('/family/:idFamily/children', "Family#children")->with('idFamily', '[0-9]+');
	$router->get('/family/:id/:champs', "Family#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/family/:id', "Family#show")->with('id', '[0-9]+');
	$router->get('/family/:champs', "Family#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/family', "Family#index");
// LE getters par excellence. Il donne tout.
	$router->post('/family/alldata', 'Family#alldata'); // donne un idFamily, et recois touutes les infos de cette familles (enfant hero settings parents etc..)

// Getters children ********************************************************
	$router->get('/children/:id/:champs', "Children#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/children/:id', "Children#show")->with('id', '[0-9]+');
	$router->get('/children/:champs', "Children#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/children', "Children#index");

// Getters parent ********************************************************
	$router->get('/parent/:id/:champs', "Parent#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/parent/:id', "Parent#show")->with('id', '[0-9]+');
	$router->get('/parent/:champs', "Parent#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/parent', "Parent#index");

// Getters hero ********************************************************
	$router->get('/hero/:id/:champs', "Hero#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/hero/:id', "Hero#show")->with('id', '[0-9]+');
	$router->get('/hero/:champs', "Hero#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/hero', "Hero#index");

// Getters Chore ********************************************************
	$router->get('/chore/:id/:champs', "Chore#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/chore/:id', "Chore#show")->with('id', '[0-9]+');
	$router->get('/chore/:champs', "Chore#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/chore', "Chore#index");

	$router->get('/choredone/:id/:champs', "Chore#showfieldsDone")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/choredone/:id', "Chore#showDone")->with('id', '[0-9]+');
	$router->get('/choredone/:champs', "Chore#indexfieldsDone")->with('champs', '[a-z\-0-9,]+');
	$router->get('/choredone', "Chore#indexDone");

// Getters Settings ********************************************************
	$router->get('/settings/:id/:champs', "Settings#showfields")->with('id', '[0-9]+')->with('champs', '[a-z\-0-9,]+');
	$router->get('/settings/:id', "Settings#show")->with('id', '[0-9]+');
	$router->get('/settings/:champs', "Settings#indexfields")->with('champs', '[a-z\-0-9,]+');
	$router->get('/settings', "Settings#index");

//  Getters Partenaire
	$router->get('/partner/familychildren/', "Partner#familychildren");
	$router->get('/partner/familychildren/:idFamily', "Partner#familychildren")->with('idFamily', '[0-9]+');
	$router->get('/partner/familychildren/:mailFamily', "Partner#familychildren");//->with('mailFamily', '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$');
	$router->get('/partner/isabletoplay/:idChild', "Partner#isAbleToPlay")->with('idChild', '[0-9]+');
	$router->get('/partner/gainxp/:idChild/:gain', "Partner#gainXp")->with('idChild', '[0-9]+')->with('gain', '[0-9]+');




// NOTIFICATION
	$router->post('/notification/checkmission', 'Notification#checkmission'); // envoie notif pour les mission du matin/midi/gouter/soir -recurrente-
	$router->post('/notification/checkpunctual', 'Notification#checkpunctual'); // envoie mission de la journée (date précise) -ponctuelle-
	$router->post('/notification/clear', 'Notification#clear'); // efface la bdd notif par famille.
	// Routing pour l'acces du cron.
	$router->get('/task/recurrent', 'Notification#recurrent');
	$router->get('/task/tryme', 'Notification#tryme');
	$router->get('/task/punctual', 'Notification#punctual');


// MAIL
	$router->post('/newpass', 'Family#askPass'); // on redirige vers la famille
	$router->get('/asknewpass/:mail', 'Mail#askNewPass');

// REPORT
	// $router->get('/dailyreport', 'Create#dailyReport');
	$router->get('/dailyreport', 'Report#dailyReport');


// ************

	$router->run();

 ?>
