<?php

/*Enregistre les boutons sur le hook voulu (page qui liste toutes les pubs)*/
add_action( 'manage_posts_extra_tablenav', 'RPJP_add_export_button', 20, 1 );

/*Ajout de boutons sur la page listant toutes les publicités*/
function RPJP_add_export_button( $which ) {
	
	// Chargement du contrôle js sur les inputs date
	wp_enqueue_script( 'rpjp-check-dates', plugins_url( '/js/date.js', __FILE__), '', '', true );
    
	global $typenow;
  
    if ( 'regie_publicitaire' === $typenow && 'top' === $which ) { //teste si l'on se trouve sur le bon post-type 
        ?>
		<!-- permet de renseigner les dates voulues pour l'export ou d'exporter tout -->
		<div class="alignleft actions">
			<b><?php echo __( 'Générer un fichier d\'export : ', 'rpjp_export' ) ?></b>
			<!--<form method="get"> Crée un conflit avec la soumission des bulk actions -->
				du
				<input type="date" id="dateDeb" name="debut"></input>
				au
				<input type="date" id="dateFin" name="fin"></input>
				<input type="submit" name="export_post_date" id="exporter" class="button button-primary" value="<?php _e('Exporter', 'rpjp-plugin'); ?>" />
				<input type="submit" name="export_all_posts" class="button button-primary" value="<?php _e('Exporter toutes les publicités', 'rpjp-plugin'); ?>" />
			
			<!--</form>-->
		</div>
        <?php
    }
}

/* Permet l'export des publicités en .csv */
add_action( 'init', 'RPJP_export_posts' );

function RPJP_export_posts() {
    if(isset($_GET['export_post_date'])) { //si le bouton "exporter" est cliqué
		$debut = strtotime($_GET['debut']);
		$fin = strtotime($_GET['fin']);
		if(isset($_GET['debut']) && isset($_GET['fin']) && $fin > $debut){
			//on prépare des paramètres pour sélectionner le type de posts voulu
			$arg = array(
				'post_type' => 'regie_publicitaire',
				'post_status' => array('publish','draft'),
				'posts_per_page' => -1,
				'meta_query' => array(
										'relation' => 'AND',
										array(
											'key'     => 'dateDeb',
											'value'   => date('Y-m-d', $debut), //Attention au format de la date pour la comparaison !!
											'type'    => 'DATE',
											'compare' => '>='
										),
										array(
											'key'     => 'dateFin',
											'value'   => date('Y-m-d', $fin), //Attention au format de la date pour la comparaison !!
											'type'    => 'DATE',
											'compare' => '<='
										),
									)
			);
			
			$my_query = new WP_Query( $arg );
			if( $my_query->have_posts() ) {
				
				//requêtes pour créer le fichier csv et gérer les dates 
				header('Content-Encoding: UTF-8');
				header('Content-type: text/csv; charset=UTF-8');
				header('Content-Disposition: attachment; filename="regie_publicitaire.csv"');
				header('Pragma: no-cache');
				header('Expires: 0');
				
				$file = fopen('php://output', 'w');
				echo "\xEF\xBB\xBF";
				
				fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Durée totale (j)', 'Référence', 'Statut')); //En-tête du fichier CSV
				
				
				while( $my_query->have_posts() ) : $my_query->the_post();
					fputcsv($file, array ( 
											get_the_title(get_the_ID()),
											date('d-m-Y', strtotime( get_post_meta( get_the_ID(), 'dateDeb', true ) )),
											date('d-m-Y', strtotime( get_post_meta( get_the_ID(), 'dateFin', true ) )),
											floor( ( strtotime( get_post_meta( get_the_ID(), 'dateFin', true )) - strtotime( get_post_meta( get_the_ID(), 'dateDeb', true )) ) / 86400),
											get_post_meta(get_the_ID(), 'ref', true),
											handleStatus(get_post( get_the_ID(), 'OBJECT' ))
										 )
							);
				endwhile;
				exit();
			} else {
				
				?>
					<div class="notice notice-warning is-dismissible" >
						<p><?php _e( 'Aucune donnée à exporter entre le '. date('d-m-Y', $debut) .' et le '. date('d-m-Y', $fin) .'.', 'rpjp-plugin' ); ?></p>
					</div>
				<?php

			}
			wp_reset_postdata();
			
		} else if( $debut > $fin ) {
			?>
				<div class="notice notice-warning is-dismissible" >
					<p><?php _e( 'La date de début ne peut pas être postérieure à la date de fin.', 'rpjp-plugin' ); ?></p>
				</div>
			<?php
		}else{
			?>
				<div class="notice notice-warning is-dismissible" >
					<p><?php _e( 'Veuillez entrer des dates.', 'rpjp-plugin' ); ?></p>
				</div>
			<?php
		}
    }
    if( isset( $_GET['export_all_posts'] ) ) { //si le bouton "exporter toutes les publicités" est cliqué
		//paramètres pour récupérer les posts regie_publicitaire
        $arg = array(
            'post_type' => 'regie_publicitaire',
            'post_status' => array('publish','draft'),
            'posts_per_page' => -1,
        );
  
        global $post;
        $arr_post = get_posts($arg); //on récupère tous les posts qui correspondent aux paramètres
        if (count($arr_post) > 0) { //s'il y en a
  
			//requêtes pour créer le fichier csv
			header('Content-Encoding: UTF-8');
            header('Content-type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="regie_publicitaire.csv"');
            header('Pragma: no-cache');
            header('Expires: 0'); 
			
			
            $file = fopen('php://output', 'w');
			echo "\xEF\xBB\xBF";
            
			fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Durée totale (j)', 'Référence', 'Statut')); //En-tête du fichier CSV
			
			
            foreach ($arr_post as $post) {
				
				fputcsv($file, array ( 
										$post->post_title,
										date('d-m-Y', strtotime($post->dateDeb)),
										date('d-m-Y', strtotime($post->dateFin)),
										floor ( ( strtotime($post->dateFin) - strtotime($post->dateDeb) ) / 86400 ),
										$post->ref,
										handleStatus($post)
						            )
						);
			}
            exit();
        } else {
			?>
				<div class="notice notice-warning is-dismissible" >
					<p><?php _e( 'Aucune donnée à exporter.', 'rpjp-plugin' ); ?></p>
				</div>
			<?php
		}
    }
}
