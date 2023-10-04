<?php

/* Initialisation des paramètres */
add_action( 'admin_init', 'RPJP_settings_init' );
 
/*Options et paramètres personnalisés*/
function RPJP_settings_init() {
    
	//enregistre un nouveau paramètre
    register_setting( 'RPJP_settings', 'RPJP_options' );
 
    //enregistre une nouvelle section de paramètres
    add_settings_section(
        'RPJP_param_section', //ID
        __( '', 'rpjp-plugin' ), //titre
		'RPJP_param_section_callback', //callback
        'RPJP_settings' //slug
    );
 
    //ajoute un nouveau champs de paramètre
	add_settings_field(
        'RPJP_taxo', //ID
        __( 'Taxonomie', 'rpjp-plugin' ), //titre
		'RPJP_create_settings_elmts', //callback
        'RPJP_settings', //slug du paramètre
        'RPJP_param_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_taxo',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
			'option_name'       => 'RPJP_options',
			'type_of_elmt'      => 'input',
			'elmt_type'         => 'text',
			'default_value'     => 'category',
			'description'       => array(
										  'class_name' => 'description',
                                          'desc_content'    => __('Entrez le nom de la taxonomie dans laquelle le terme parent sera récupéré. (Par défaut "category")', 'rpjp-plugin')
								   )
        )
    );
	
    add_settings_field(
        'RPJP_parent', //ID
        __( 'Terme parent', 'rpjp-plugin' ), //titre
		'RPJP_create_settings_elmts', //callback
        'RPJP_settings', //slug
        'RPJP_param_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_parent',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
			'option_name'       => 'RPJP_options',
			'type_of_elmt'      => 'input',
			'elmt_type'         => 'text',
			'default_value'     => '',
			'description'       => array(
										  'class_name' => 'description',
                                          'desc_content'    => __('Entrez le nom du terme mère qui permet de filtrer les pubs sur un même CPT.', 'rpjp-plugin')
								   )
        )
    );
	
	add_settings_field(
        'RPJP_size', //ID
        __( 'Version mobile', 'rpjp-plugin' ), //titre
		'RPJP_create_settings_elmts', //callback
        'RPJP_settings', //slug
        'RPJP_param_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_size',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
			'option_name'       => 'RPJP_options',
			'type_of_elmt'      => 'input',
			'elmt_type'         => 'number',
			'default_value'     => '',
			'placeholder'       => 'Recommandée: 992',
			'description'       => array(
										  'class_name' => 'description',
                                          'desc_content'    => __('Entrez une largeur en PX en dessous de laquelle s\'affichera la version mobile.', 'rpjp-plugin')
								   )
        )
    );
	
	add_settings_field(
        'RPJP_prefixe', //ID
        __( 'Préfixe (optionnel)', 'rpjp-plugin' ), //titre
		'RPJP_create_settings_elmts', //callback
        'RPJP_settings', //slug
        'RPJP_param_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_prefixe',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
			'option_name'       => 'RPJP_options',
			'type_of_elmt'      => 'input',
			'elmt_type'         => 'text',
			'default_value'     => '',
			'description'       => array(
										  'class_name' => 'description',
                                          'desc_content'    => __('Entrez un préfixe pour la génération automatique des références.', 'rpjp-plugin')
								   )
        )
    );
		add_settings_field(
        'RPJP_suffixe', //ID
        __( 'Suffixe (optionnel)', 'rpjp-plugin' ), //titre
	    'RPJP_create_settings_elmts', //callback
        'RPJP_settings', //slug
        'RPJP_param_section', //section où le champ se trouve
        array(
			'label_for'         => 'RPJP_suffixe',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
			'option_name'       => 'RPJP_options',
			'type_of_elmt'      => 'input',
			'elmt_type'         => 'text',
			'default_value'     => '',
			'description'       => array(
										  'class_name' => 'description',
                                          'desc_content'    => __('Entrez un suffixe pour la génération automatique des références.', 'rpjp-plugin')
								   )
        )
    );
	add_settings_field (
			'RPJP_push', //ID
			__( 'Remontée des clics', 'rpjp-plugin' ), //titre
			'RPJP_create_settings_radio', //callback
			'RPJP_settings', //slug
			'RPJP_param_section', //section où le champ se trouve
			array (
				'label_for'         => 'RPJP_push',
				'class'             => 'RPJP_row',
				'RPJP_custom_data'  => 'custom',
				'option_name'       => 'RPJP_options',
				'description'       => array (
										'class_name'   => 'description',
										'desc_content' => __('Choisissez une plateforme d\'analyse du trafic web', 'rpjp-plugin')
									   )
			)
		);
}
 
/* Fonction de callback de la section */
function RPJP_param_section_callback( $args ) {
    ?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo __('Section de réglages avancés de l\'extension réservée au webmaster.', 'rpjp-plugin'); ?></p>
    <?php
}
/* Fonction callback qui gère la création des champs du formulaire */
function RPJP_create_settings_elmts( $args ){
	$options = get_option( $args['option_name'], array() ); //récupère les options du groupe d'options
	
	if ( $args['type_of_elmt'] == 'input' ) {
		?>
			<input 
				   type="<?php echo esc_attr( $args['elmt_type'] ); ?>"
				   <?php echo isset( $args['required'] ) && $args['required'] === 'on' ? 'required="required"' : ''; ?>
				   <?php echo isset( $args['label_for'] ) && $args['label_for'] != '' ? 'id="' . esc_attr( $args['label_for'] ) . '"' : ''; ?>
				   data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
				   name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				   <?php echo isset( $args['placeholder'] ) && $args['placeholder'] != '' ? 'placeholder="' . esc_attr( $args['placeholder'] ) . '"' : ''; ?>
				   value="<?php echo isset( $options[$args['label_for']] ) && $options[$args['label_for']] != '' ?  $options[$args['label_for']] : esc_attr( $args['default_value'] ); ?>">
			</input>
			<?php 
			if ( isset( $args['description'] ) && $args['description'] != '' ) {
				?>
					<p class="<?php echo esc_attr( $args['description']['class_name'] ); ?>"><?php echo $args['description']['desc_content'] ?></p>
				<?php
			}
	}
}

/* Fonction callback qui gère la création de boutons radio */
function RPJP_create_settings_radio( $args ){
	
	$options = get_option( $args['option_name'], array() ); //récupère les options du groupe d'options
	
	if ( ! isset ($options['radio_event_push']) ) {
		$options['radio_event_push'] = 1;
	}
	
	$html = '<input type="radio" id="noPush" name="RPJP_options[radio_event_push]" value="1"' . checked( 1, $options['radio_event_push'], false ) . '/>';
	$html .= '<label for="noPush">Aucune</label><br>';
	
	$html .= '<input type="radio" id="ga4" name="RPJP_options[radio_event_push]" value="2"' . checked( 2, $options['radio_event_push'], false ) . '/>';
	$html .= '<label for="ga4">Analytics 4</label><br>';
	
	$html .= '<input type="radio" id="matomo" name="RPJP_options[radio_event_push]" value="3"' . checked( 3, $options['radio_event_push'], false ) . '/>';
	$html .= '<label for="matomo">Matomo</label><br>';
	
	$html .= '<p class="' .  esc_attr ( $args['description']['class_name'] ) . '">' . esc_attr ( $args['description']['desc_content'] ) . '</p>';
	
	echo $html;
	
}

/*Enregistre la page à l'aide d'un hook*/
add_action( 'admin_menu', 'RPJP_options_page' );

/*Ajout la page dans le menu admin en tant que submenu du post-type regie_publicitaire*/
function RPJP_options_page() {
	add_submenu_page(
		'edit.php?post_type=regie_publicitaire',
		'Réglages avancés',
        'Réglages',
        'manage_options',
        'RPJP_settings',
        'wporg_options_page_html'
	);
} 
 
/*Fonction de callback pour afficher tous les éléments dans la page*/
function wporg_options_page_html() {
    //vérifie les droits de l'utilisateur
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    //vérifie que l'utilisateur a passé des paramètres
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'wporg_messages', 'wporg_message', __( 'Paramètres sauvegardés', 'rpjp-plugin' ), 'updated' ); //affiche un message pour confirmer l'enregistrement des paramètres
    }
 
    settings_errors( 'wporg_messages' ); //affiche les messages d'actualisation ou d'erreur
    //affiche le contenu de la page ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php 
            settings_fields( 'RPJP_settings' ); //affiche les encards de paramètres créés plus tôt
            do_settings_sections( 'RPJP_settings' ); //affiche les sections de paramètres
            submit_button( 'Enregistrer' ); //affiche un bouton de sauvegarde
            ?>
        </form>
    </div>
    <?php
}
