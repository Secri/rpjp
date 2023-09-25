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
					
					//Gestion du renouvellement automatique
					$custom = get_post_custom($value->ID); //On récupère les paramètres custom dans un tableau
					if(isset( $custom["renew"][0] ) && $custom["renew"][0] == 'on' ) { //Si le post qui vient d'être dépublié est en mode renouvellement auto
						//calculer la durée de la pub initiale
						$duration = strtotime ( $value->dateFin ) - strtotime ( $value->dateDeb );
						//Tableau des données du post à créer 
						$postarr = array(
											'post_title'  => $value->post_title,
											'post_status' => 'draft',            //en draft (pour pouvoir récupérer l'ID et générer la ref
											'post_author'  => 1,
											'post_type'   => 'regie_publicitaire',
											'meta_input'  => array(
																	'ref'           => '',
																	'image_desktop' => $value->image_desktop,
																	'image_mobile'  => $value->image_mobile,
																	'cpt'           => $value->cpt,
																	'categ'         => $value->categ,
																	'follow'        => $value->follow,
																	'mobile'        => $value->mobile,
																	'renew'         => $value->renew,
																	'lien'          => $value->lien,
																	'dateDeb'       => date( 'Y-m-d', time() ),
																	'dateFin'       => date( 'Y-m-d', time() + $duration )
																   ),
										);
						$newPostId = wp_insert_post($postarr, true); //On lance l'insertion qui retourne l'ID du post ainsi créé
						if ( $newPostId && !is_wp_error( $newPostId ) ) { //Si l'opération s'est bien déroulée
							//Génération de la ref
							$options = get_option('RPJP_options', array()); 
							$fullPrefixe = $options['RPJP_prefixe'] != '' ? $options['RPJP_prefixe'] . '-' : '';
							$fullSuffixe = $options['RPJP_suffixe'] != '' ? '-' . $options['RPJP_suffixe'] : '';
							update_post_meta( $newPostId, 'ref', sanitize_text_field( strtoupper($fullPrefixe) . get_the_date('mY', $newPostId) . "-" . $newPostId . strtoupper($fullSuffixe) ) );
							//Le post est complet donc on peut le passer en publish
							$arg = array(
									'ID' => $newPostId,
									'post_status' => 'publish',
								);
							wp_update_post( $arg ); //on passe le post en publié
						}
					}
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
