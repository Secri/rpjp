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
		<div>|&nbsp;&nbsp;&nbsp;
			<b><?php echo __( 'Générer un fichier d\'export : ', 'rpjp_export' ) ?></b>
			<form method="get">
				
				<input type="date" id="dateDeb" name="debut"></input>
				<input type="date" id="dateFin" name="fin"></input>
				
				<input type="submit" name="export_post_date" id="exporter" class="button button-primary" value="<?php _e('Exporter'); ?>" />
			</form>
			<input type="submit" name="export_all_posts" class="button button-primary" value="<?php _e('Exporter toutes les publicités'); ?>" />
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
			);
		  
			global $post;
			$arr_post = get_posts($arg); //on récupère tous les posts qui correspondent aux paramètres
			if ($arr_post) { //s'il y en a
		  
				//requêtes pour créer le fichier csv et gérer les dates 
				header('Content-Encoding: UTF-8');
				header('Content-type: text/csv; charset=UTF-8');
				header('Content-Disposition: attachment; filename="regie_publicitaire.csv"');
				header('Pragma: no-cache');
				header('Expires: 0');
				
				$file = fopen('php://output', 'w');
				echo "\xEF\xBB\xBF";
				//fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Post-type d\'affichage', 'Catégorie d\'affichage', 'Référence', 'Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
				fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Référence', 'Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
	  
				//pour chaque post, récupère et affiche les données
				foreach ($arr_post as $post) {
					
					if ( strtotime($post->dateDeb) >= $debut && strtotime($post->dateFin) <= $fin ) {
						/*fputcsv($file, array (
												$post->post_title,
												$post->dateDeb,
												$post->dateFin,
												$post->cpt,
												$post->categ,
												$post->ref,
												handleStatus($post)
											)
								);*/
						fputcsv($file, array ( 
												$post->post_title,
												date('d-m-Y', strtotime($post->dateDeb)),
												date('d-m-Y', strtotime($post->dateFin)),
												$post->ref,
												handleStatus($post)
											)
								);
					}
				}
				exit();
			}
		}else if($debut > $fin){
			?>
			<div class="notice error my-acf-notice is-dismissible" >
				<p><?php _e( 'La date de début ne peut pas être postérieure à la date de fin.', 'RPJP' ); ?></p>
			</div>
			<?php
		}else{
			?>
			<div class="notice error my-acf-notice is-dismissible" >
				<p><?php _e( 'Veuillez entrer des dates.', 'RPJP' ); ?></p>
			</div>
			<?php
		}
    }
    if(isset($_GET['export_all_posts'])) { //si le bouton "exporter toutes les publicités" est cliqué
		//on prépare des paramètres pour sélectionner le type de posts voulu
        $arg = array(
            'post_type' => 'regie_publicitaire',
            'post_status' => array('publish','draft'),
            'posts_per_page' => -1,
        );
  
        global $post;
        $arr_post = get_posts($arg); //on récupère tous les posts qui correspondent aux paramètres
        if ($arr_post) { //s'il y en a
  
			//requêtes pour créer le fichier csv
			header('Content-Encoding: UTF-8');
            header('Content-type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="regie_publicitaire.csv"');
            header('Pragma: no-cache');
            header('Expires: 0'); 
			
			
            $file = fopen('php://output', 'w');
			echo "\xEF\xBB\xBF";
            //fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Post-type d\'affichage', 'Catégorie d\'affichage', 'Référence', 'Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
			fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Référence', 'Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
			
			//pour chaque post, récupère et affiche les données
            foreach ($arr_post as $post) {
				
				/*fputcsv($file, array ( 
										$post->post_title,
										$post->dateDeb,
										$post->dateFin,
										$post->cpt,
										$post->categ,
										$post->ref,
										handleStatus($post)
										)
						);*/
				fputcsv($file, array ( 
										$post->post_title,
										date('d-m-Y', strtotime($post->dateDeb)),
										date('d-m-Y', strtotime($post->dateFin)),
										$post->ref,
										handleStatus($post)
										)
						);
			}
            exit();
        }
    }
}