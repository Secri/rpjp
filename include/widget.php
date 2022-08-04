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
	
	/* Gère l'affichage du widget en front */
	public function widget( $args, $instance ) {
		//$title = apply_filters( 'widget_title', $instance['title'] ); // NON NECESSAIRE !!
		echo $args['before_widget'];
		//Affiche le titre si il est défini
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];		
			
		// fonction qui crée un élément HTML img à partir de son URL et du terminal client
		function rpjp_display_img( $image_url, $display ) {
			if ( $display == 'mobile' ) {
				echo wp_get_attachment_image ( attachment_url_to_postid( $image_url ) , 'Full size', false, array( 'class' => 'imageMobile' ) );
			} else {
				echo wp_get_attachment_image ( attachment_url_to_postid( $image_url ) , 'Full size', false, array( 'class' => 'imageDesktop' ) );
			}
		}
		
		// fonction qui crée le bouton svg de fermeture
		function rpjp_svg_elt() {
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
		
		function display_the_add( $currentId ) { // Crée l'ensemble de la pub et l'affiche dans le widget en fonction de l'ID du cpt regie_publicitaire
			
			echo '<div class="RPJP_mobile">';

			if(get_post_meta( $currentId, 'follow', true ) == "on"){

				echo '<a rel="nofollow" class="lien" href=' . get_post_meta( $currentId, 'lien', true ) . '>'; //crée un élément <a> et récupère l\'URL passée en paramètre

				echo rpjp_display_img( wp_get_attachment_image_url(get_post_meta( $currentId, 'image_desktop', true ), 'Full Size'), 'desktop' );

				if(get_post_meta( $currentId, 'mobile', true ) == "on"){

					wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile

					echo rpjp_display_img( wp_get_attachment_image_url( get_post_meta( $currentId, 'image_mobile', true ),'Full Size' ), 'mobile' );

					echo '</a>';

					rpjp_svg_elt(); //Bouton qui permet de fermer la publicité sur mobile

				} else {

					echo '</a>';

				}

			} else {

				echo '<a class="lien" href=' . get_post_meta( $currentId, 'lien', true ) . '>'; // ajoute le rel=nofollow

				echo rpjp_display_img( wp_get_attachment_image_url(get_post_meta($currentId, 'image_desktop', true), 'Full Size'), 'desktop' );

				if(get_post_meta( $currentId, 'mobile', true ) == "on"){

					wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile

					echo rpjp_display_img( wp_get_attachment_image_url(get_post_meta($currentId, 'image_mobile', true),'Full Size'), 'mobile' );

					echo '</a>';

					rpjp_svg_elt(); //Bouton qui permet de fermer la publicité sur mobile

				} else {

					echo '</a>';

				}

			}

			echo '</div>';
		}
		
		// Algorithme conditionnel d'affichage des pubs sur les posts
		global $wp_query; //on fait de $wp_query une variable globale
		$idP = $wp_query->post->ID; //on stocke l'id de la page où l'on se situe
		
		$options = get_option( 'RPJP_options', array() ); //on récupère les données de la page d'options
		
		//Filtre les pages en fonction du post-type et des paramètres donnés
		$all = new WP_Query( apply_filters( 'widget_posts_args', array(
				'post_type'           => 'regie_publicitaire',
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'posts_per_page'      => -1
		) ) );
		
		if ($all->have_posts()) :
		
			while ( $all->have_posts() ) : $all->the_post(); //tant qu'il y a des posts dans le post-type
			
				if(get_post_type($idP) == get_post_meta( get_the_ID(), 'cpt', true )){ //teste si le post-type de la page actuelle correspond au post-type passé en paramètre
				
					echo '<div class="get_select" style="display:none">' . $options['RPJP_div'] . '</div>'; // Données pour exploitation js
					echo '<div class="get_size" style="display:none">' . $options['RPJP_size'] . '</div>';
				
					if(get_post_meta( get_the_ID(), 'categ', true ) != 'toutes' || null){ //teste si le champ possède une valeur autre que celle par défaut ou null	
					
						$args_posts = array( //tableau de conditions pour récupérer les bons posts
								'post_type'		=> get_post_meta( get_the_ID(), 'cpt', true ),
								'tax_query'		=> array( //récupère les posts avec le terme de la taxonomie passé en paramètre
															array(
																	'taxonomy'	=> $options['RPJP_taxo'],
																	'field'		=> 'slug',
																	'terms'		=> get_post_meta( get_the_ID(), 'categ', true )
																),
														),
									);
									
						$rpjp_valid_posts = get_posts( $args_posts ); //récupère les posts correspondants aux paramètres
						
						foreach( $rpjp_valid_posts as $value ){ //parcours tout ces posts
							
							if($idP == $value->ID) { //si l'id du post actuel correspond à un id de la liste

								if ( handleStatus( $all->post ) == 'Publiée' ) { //si la publicité est encore valide et a commencé
								
									display_the_add( get_the_ID() );
									
								}
							}
						}
						
					} else {
						
						if ( handleStatus( $all->post ) == 'Publiée' ) { //si la publicité est encore valide et a commencé
								
							display_the_add( get_the_ID() );	
						}
					}
				}
				
			endwhile;
			
			wp_reset_postdata(); // Reset la variable globale $the_post 
			
		endif;

		echo $args['after_widget'];
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