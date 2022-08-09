<?php
function rpjp_register_widget() {
	register_widget( 'rpjp_widget' );
}

add_action( 'widgets_init', 'rpjp_register_widget' );

class rpjp_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// widget ID
			'rpjp_widget',
			// widget name
			__('Régie publicitaire CCI89', ' rpjp_widget_domain'),
			// widget description
			array( 'description' => __( 'Permet l\'ajout de publicités avec le custom post-type "régie publicitaire"', 'rpjp_widget_domain' ), )
			);
	}
	private function getPub($idP,$categ = 'toutes'){
		$pubs = new WP_Query( apply_filters( 'widget_posts_args', array(
			'post_type'           => 'regie_publicitaire',
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
			'meta_query' => array(
				'relation'	=> 'AND',
				array(
					'key'     => 'cpt',
					'value'   => get_post_type($idP),
					'type'	  => 'char',
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'categ',
					'value'   => $categ,
					'type'	  => 'char',
					'compare' => 'LIKE',
				),	
			),
		) ) );
		return $pubs;
	}
	/* Gère l'affichage du widget en front */
	public function widget( $args, $instance ) {
		//$title = apply_filters( 'widget_title', $instance['title'] ); // NON NECESSAIRE !!
		
		echo $args['before_widget'];
		
		//Affiche le titre si il est défini
		if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];		
		
		// Algorithme conditionnel d'affichage des pubs sur les posts
		global $wp_query; //on fait de $wp_query une variable globale
		$idP = $wp_query->post->ID; //on stocke l'id de la page où l'on se situe
		
		$options = get_option( 'RPJP_options', array() ); //on récupère les données de la page d'options
		
		//On cherche si la page en front possède une categ (terme de la taxonomie définie en options) en commun avec une pub RPJP
		
		$categ = ''; //Initialisation de la variable
		
		if(isset($options['RPJP_taxo'])){ // Si une taxonomie a été définie dans les options
			
			$parent_term_id = get_term_by('slug', $options['RPJP_parent'], $options['RPJP_taxo'])->term_id; // On récupère l'id du terme parent défini dans les options
			
			$child_terms_id_array = get_term_children( $parent_term_id,  $options['RPJP_taxo']); // Tableau des id des termes enfants existants
			
			$tempo = get_the_terms( get_the_ID(), $options['RPJP_taxo'] ); // Tableau d'objets des termes de taxo de la page en front
			
			if(is_array($tempo) && is_object($tempo[0]) && isset($tempo[0]->slug)) { // Si la page en front possède des termes de taxonomie
				
				$categ = $tempo[0]->slug; // Renvoie le 1er terme associé à la taxonomie "RPJP_taxo" de la page courante
				
				foreach( $tempo as $value) { //On parcourt le tableau d'objets des termes
					
					foreach( $child_terms_id_array as $term_id ){ // On le compare avec le tableau d'ID des termes
						
						if ( get_term( $term_id )->slug == $value->slug ) { // Si un slug est commun aux deux tableaux
							
							$categ = $value->slug; // On pousse le slug dans la variable
							
							break; //On arrête la boucle (inutile de continuer)
						}
						
					}

				}
				
			} else {
				
				$categ = 'no term associated to the current post'; // On associe un nom au cas où la page en front n'aurait pas de termes associés
			
			}
		}
		
		unset( $parent_term_id );         // On détruit les variables temporaires
		unset( $child_terms_id_array );
		unset($tempo); 
		
		//On recherche la pub de la categ
		$pub = $this->getPub( $idP, $categ );

		if( $pub->post_count == 0 && $categ != "" ){ // Si la recherche de posts RPJP ne renvoie rien
			//On recherche la pub pour "toutes"
			$pub = $this->getPub($idP);
		}

		if($pub->have_posts()){
			$pub->the_post();
			//On passe les infos get_size pour mobile.js
			echo '<div class="get_size" style="display:none">'.$options['RPJP_size'].'</div>';
			//On affiche la pub
			$this->display_the_add( get_the_ID() );
		}
		
		echo $args['after_widget'];
		wp_reset_postdata(); // Reset la variable globale $the_post 
	}

	// fonction qui crée un élément HTML img à partir de son URL et du terminal client
	private function rpjp_display_img( $image_url, $display ) {
		if ( $display == 'mobile' ) {
			echo wp_get_attachment_image ( attachment_url_to_postid( $image_url ) , 'Full size', false, array( 'class' => 'imageMobile' ) );
		} else {
			echo wp_get_attachment_image ( attachment_url_to_postid( $image_url ) , 'Full size', false, array( 'class' => 'imageDesktop' ) );
		}
	}
		
	// fonction qui crée le bouton svg de fermeture
	private function rpjp_svg_elt() {
		echo '<svg class="rpjp_svg" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin">
					<style scoped>
						.rpjp_svg{
							width:20px;
							height:20px;
							display:none;
							position : fixed;
							left: 5px;
						}
					</style>
					<g fill="rgba(237,237,237,1)">
						<path d="M11.414 10l2.829-2.828a1 1 0 1 0-1.415-1.415L10 8.586 7.172 5.757a1 1 0 0 0-1.415 1.415L8.586 10l-2.829 2.828a1 1 0 0 0 1.415 1.415L10 11.414l2.828 2.829a1 1 0 0 0 1.415-1.415L11.414 10zM10 20C4.477 20 0 15.523 0 10S4.477 0 10 0s10 4.477 10 10-4.477 10-10 10z" />
					</g>
			  </svg>';
	}
		
	private function display_the_add( $currentId ) { // Crée l'ensemble de la pub et l'affiche dans le widget en fonction de l'ID du cpt regie_publicitaire
			
		echo '<div class="RPJP_img_cont">';

		if(get_post_meta( $currentId, 'follow', true ) == "on"){

			echo '<a rel="nofollow" class="lien" href=' . get_post_meta( $currentId, 'lien', true ) . '>'; //crée un élément <a> et récupère l\'URL passée en paramètre

			echo $this->rpjp_display_img( wp_get_attachment_image_url(get_post_meta( $currentId, 'image_desktop', true ), 'Full Size'), 'desktop' );

			if(get_post_meta( $currentId, 'mobile', true ) == "on"){

				wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile

				echo $this->rpjp_display_img( wp_get_attachment_image_url( get_post_meta( $currentId, 'image_mobile', true ),'Full Size' ), 'mobile' );

				echo '</a>';

				$this->rpjp_svg_elt(); //Bouton qui permet de fermer la publicité sur mobile

			} else {

				echo '</a>';

			}

		} else {

			echo '<a class="lien" href=' . get_post_meta( $currentId, 'lien', true ) . '>'; // ajoute le rel=nofollow

			echo $this->rpjp_display_img( wp_get_attachment_image_url(get_post_meta($currentId, 'image_desktop', true), 'Full Size'), 'desktop' );

			if(get_post_meta( $currentId, 'mobile', true ) == "on"){

				wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile

				echo $this->rpjp_display_img( wp_get_attachment_image_url(get_post_meta($currentId, 'image_mobile', true),'Full Size'), 'mobile' );

				echo '</a>';

				$this->rpjp_svg_elt(); //Bouton qui permet de fermer la publicité sur mobile

			} else {

				echo '</a>';

			}

		}

		echo '</div>';
	}

	/*Gestion de l'affichage des options sur le backoffice */
	public function form( $instance ) {
        echo '<br>Ce widget est automatique et ne nécessite pas de paramètres.';
	}
	
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
		//Pas de MAJ de l'instance puisque pas de paramètres
	}

}
