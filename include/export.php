<?php

/*Enregistre les boutons sur le hook voulu (page qui liste toutes les pubs)*/
add_action( 'manage_posts_extra_tablenav', 'RPJP_add_export_button', 20, 1 );

/*Ajout de boutons sur la page listant toutes les publicités*/
function RPJP_add_export_button( $which ) {
	wp_enqueue_script( 'RPJP-export', plugins_url( '/js/export.js', __FILE__), '', '', true );
    global $typenow;
  
    if ( 'regie_publicitaire' === $typenow && 'top' === $which ) { //teste si l'on se trouve sur le bon post-type 
        ?>
		<!-- permet de renseigner les dates voulues pour l'export ou d'exporter tout -->
		<div>|&nbsp;&nbsp;&nbsp;
		<input type="submit" name="export_post_date" disabled class="button" value="<?php _e('Options d\'exportation'); ?>"/>
		<form method="get">
			<input type="date" id="debut" name="debut" onchange="end_form()"></input>
			<input type="date" id="fin" name="fin" onchange="end_form()"></input>
			<input type="submit" name="export_post_date" id="exporter" class="button button-primary" value="<?php _e('Exporter'); ?>" />
		</form>
        <input type="submit" name="export_all_posts" class="button button-primary" value="<?php _e('Exporter toutes les publicités'); ?>" />
		</div>
        <?php
    }
}

/*Enregistre la fonction*/
add_action( 'init', 'RPJP_export_posts' );

/*Fonction qui permet l'export des publicités en .csv*/
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
				fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Post-type d\'affichage', 'Catégorie d\'affichage', 'Référence', 'Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
	  
				//pour chaque post, récupère et affiche les données
				foreach ($arr_post as $post) {
					setup_postdata($post);
					$expirationtime = get_post_custom_values('dateFin'); //récupère la date de fin
					$debu = get_post_custom_values('dateDeb'); //récupère la date de début
					if (is_array($expirationtime) || is_array($debu)) {
						$expirestring = implode($expirationtime);
						$debstring = implode($debu);
					}
					$secondsbetween = strtotime($expirestring)-time();
					$deb = strtotime($debstring)-time();
					if ( $deb > 0 && $secondsbetween > $deb){ //si la date de début n'est pas encore arrivée et que les paramètres sont valides
						$statut=  "À venir";//on met le statut "à venir"
					}else if(get_post_status(get_the_ID()) == 'draft' && $secondsbetween > $deb){  //si le post est en statut brouillon et que les paramètres sont valides
							$statut = "Brouillon";//on met le statut "brouillon"
					}else if(get_post_status(get_the_ID()) == 'publish' && $secondsbetween > 0 && $secondsbetween > $deb){ //si la publicité est publiée, a commencé et a des paramètres valides
							$statut = "Publiée";//on met le statut "publiée"
					}else if(get_post_status(get_the_ID()) == 'publish' && $secondsbetween < 0 && $secondsbetween > $deb){ //si sa date de fin est dépassée et que les paramètres sont valides
						$statut = "Dépassée";//on met le statut "dépassée"
					}else if($deb > $secondsbetween){ //si les paramètres sont invalides
						$statut = "Erreur";//on met le statut "erreur"
					}
					if(strtotime(get_post_meta( get_the_ID(), 'dateDeb', true )) >= $debut && strtotime(get_post_meta( get_the_ID(), 'dateDeb', true )) <= $fin){				
						fputcsv($file, array(get_the_title(), get_post_meta( get_the_ID(), 'dateDeb', true ), get_post_meta( get_the_ID(), 'dateFin', true ), get_post_meta( get_the_ID(), 'cpt', true ), get_post_meta( get_the_ID(), 'categ', true ),get_post_meta( get_the_ID(), 'ref', true ),$statut));
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
            fputcsv($file, array('Titre', 'Date de début', 'Date de fin', 'Post-type d\'affichage', 'Catégorie d\'affichage','Référence','Statut')); //ajoute une ligne indiquant à quoi correspondent les valeurs
  
			//pour chaque post, récupère et affiche les données
            foreach ($arr_post as $post) {
                setup_postdata($post);
				$expirationtime = get_post_custom_values('dateFin'); //récupère la date de fin
				$debu = get_post_custom_values('dateDeb'); //récupère la date de début
				if (is_array($expirationtime) || is_array($debu)) {
					$expirestring = implode($expirationtime);
					$debstring = implode($debu);
				}
				$secondsbetween = strtotime($expirestring)-time();
				$deb = strtotime($debstring)-time();
				if ( $deb > 0 && $secondsbetween > $deb){ //si la date de début n'est pas encore arrivée et que les paramètres sont valides
					$statut=  "À venir";//on met le statut "à venir"
				}else if(get_post_status(get_the_ID()) == 'draft' && $secondsbetween > $deb){  //si le post est en statut brouillon et que les paramètres sont valides
						$statut = "Brouillon";//on met le statut "brouillon"
				}else if(get_post_status(get_the_ID()) == 'publish' && $secondsbetween > 0 && $secondsbetween > $deb){ //si la publicité est publiée, a commencé et a des paramètres valides
						$statut = "Publiée";//on met le statut "publiée"
				}else if(get_post_status(get_the_ID()) == 'publish' && $secondsbetween < 0 && $secondsbetween > $deb){ //si sa date de fin est dépassée et que les paramètres sont valides
					$statut = "Dépassée";//on met le statut "dépassée"
				}else if($deb > $secondsbetween){ //si les paramètres sont invalides
					$statut = "Erreur";//on met le statut "erreur"
				}
				fputcsv($file, array(get_the_title(), get_post_meta( get_the_ID(), 'dateDeb', true ), get_post_meta( get_the_ID(), 'dateFin', true ), get_post_meta( get_the_ID(), 'cpt', true ), get_post_meta( get_the_ID(), 'categ', true ),get_post_meta( get_the_ID(), 'ref', true ),$statut));
			}
  
            exit();
        }
    }
}