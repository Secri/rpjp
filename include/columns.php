<?php

/*Suppression de la colonne par défaut "date"*/
add_filter('manage_regie_publicitaire_posts_columns', function ( $columns ){
    unset($columns['date']);
    return array_merge($columns, 
	array(
			'dateDeb' => __('Date de début'),
			'dateFin' => __('Date de fin'),
			'cpt'     => __('Type de contenu'),
			'categ'   => __('Catégorie'),
			'ref' => __('Référence'),
			'statut'  => __('Statut')
		)
	);
} );

add_action( 'manage_regie_publicitaire_posts_custom_column' , 'RPJP_data_colonne' );

function RPJP_data_colonne($name) {

	global $post;
	switch ($name) {
		case 'dateDeb': //affiche la date de début de la publicité
			echo esc_attr( date('d/m/Y', strtotime( get_post_meta( get_the_ID(), 'dateDeb', true ) ) ) );
		break;
		case 'dateFin': //affiche la date de fin de la publicité
			echo esc_attr( date('d/m/Y', strtotime( get_post_meta( get_the_ID(), 'dateFin', true ) ) ) );
		break;
		case 'cpt': //affiche le post-type où apparaitra la publicité
			echo esc_attr( get_post_meta( get_the_ID(), 'cpt', true ));
		break;
		case 'categ': //affiche la catégorie où apparaitra la publicité
			echo esc_attr( get_post_meta( get_the_ID(), 'categ', true ));
		break;
		case 'ref': //affiche la référence de la publicité
			echo esc_attr( get_post_meta( get_the_ID(), 'ref', true ));
		break;
		case 'statut': echo esc_attr ( handleStatus($post) );
		break;
	}
}