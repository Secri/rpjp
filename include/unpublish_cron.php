<?php 
	add_action('wp_loaded', function() { // On hook la fin du chargement de wordpress
		add_action( 'rpjp_cron_hook', 'rpjp_cron_fct'); // On créé un hook perso qui lance la fonction de dépublication
		register_deactivation_hook( __FILE__, 'rpjp_cron_deactivate'); // On désactive le cron si le plugin est désactivé
		if ( ! wp_next_scheduled('rpjp_cron_hook')) { // On vérifie que la tâche n'est pas déjà lancée
			wp_schedule_event( time(), 'hourly', 'rpjp_cron_hook' ); // On lance le cron (mettre à twicedaily ?)
		}
	});
	
	// Fonction de dépublication
	function rpjp_cron_fct() { 
		$args = array( // On cherche tous les posts de type regie_publicitaire qui sont publiés
						'post_type'  => 'regie_publicitaire',
						'post_status' => 'publish',
						'posts_per_page' => -1,
				);
		$query = new WP_Query( $args );	// on lance la requête
		if ( $query->post_count > 0 ) { // si la requête retourne au moins un post 
			foreach($query->posts as $value){ // on parcourt le tableau des objets post qui ont été retournés
				if ( strtotime( $value->dateFin ) < time() ) { //si la meta "date de fin" de l'objet courant est inférieure à la date courante
					$arg = array(
									'ID' => $value->ID,
									'post_status' => 'draft',
								);
					wp_update_post( $arg ); //on passe le post en brouillon
				}
			}
		}
		
		// On réinitialise à la requête principale (important)
		wp_reset_postdata();
	}

	// Annule la tâche si le plugin est désactivé
	function rpjp_cron_deactivate() {
		wp_clear_scheduled_hook( 'rpjp_cron_hook');
	}
