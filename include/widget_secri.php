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
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		//Affiche le titre si il est défini
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		//Output - C'est là qu'on fait des ketrus pour afficher les images sur les bonnes pages t'as vu ?
		echo __( 'kikoo loooooool', 'rpjp_widget_domain' );
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
