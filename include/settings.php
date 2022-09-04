<?php

/*Enregistre notre zone de paramètres*/
add_action( 'admin_init', 'RPJP_settings_init' );
 
/*Options et paramètres personnalisés*/
function RPJP_settings_init() {
    //enregistre un nouveau paramètre
    register_setting( 'RPJP_settings', 'RPJP_options' );
 
    //enregistre une nouvelle section de paramètres
    add_settings_section(
        'RPJP_section', //ID
        __( '', 'rpjp-plugin' ), //titre
		'RPJP_section_callback', //callback
        'RPJP_settings' //slug
    );
 
    //ajoute un nouveau champs de paramètre
	add_settings_field(
        'RPJP_taxo', //ID
        __( 'Taxonomie', 'rpjp-plugin' ), //titre
        'RPJP_field_taxo', //callback
        'RPJP_settings', //slug
        'RPJP_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_taxo',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
        )
    );
	
    add_settings_field(
        'RPJP_parent', //ID
        __( 'Terme parent', 'rpjp-plugin' ), //titre
        'RPJP_field_parent', //callback
        'RPJP_settings', //slug
        'RPJP_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_parent',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
        )
    );
	
	add_settings_field(
        'RPJP_size', //ID
        __( 'Version mobile', 'rpjp-plugin' ), //titre
        'RPJP_field_size', //callback
        'RPJP_settings', //slug
        'RPJP_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_size',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
        )
    );
	
	add_settings_field(
        'RPJP_prefixe', //ID
        __( 'Préfixe (optionnel)', 'rpjp-plugin' ), //titre
        'RPJP_field_prefixe', //callback
        'RPJP_settings', //slug
        'RPJP_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_prefixe',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
        )
    );
		add_settings_field(
        'RPJP_suffixe', //ID
        __( 'Suffixe (optionnel)', 'rpjp-plugin' ), //titre
        'RPJP_field_suffixe', //callback
        'RPJP_settings', //slug
        'RPJP_section', //section où le champ se trouve
        array(
            'label_for'         => 'RPJP_suffixe',
            'class'             => 'RPJP_row',
            'RPJP_custom_data' 	=> 'custom',
        )
    );
}
 
/*Fonction de callback*/
function RPJP_section_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Section de réglage de l\'extension réservée au webmaster.', 'rpjp-plugin' ); ?></p>
    <?php
}
 
/*Fonctions qui affichent l'encard de texte pour y entrer son paramètre*/
function RPJP_field_taxo( $args ){
	$options = get_option('RPJP_options', array()); //récupère les options créées
    //créer un input de texte pour y entrer la taxonomie voulue
	?>
    <input  type="text"  
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
            name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			placeholder="category"
			value="<?php echo isset( $options['RPJP_taxo'] ) && $options['RPJP_taxo'] != '' ?  $options['RPJP_taxo'] : 'category'; ?>">
    </input>
    <p class="description">
        <?php esc_html_e( 'Entrez le nom de la taxonomie dans laquelle le terme parent sera récupéré. (Par défaut "category")', 'rpjp-plugin' ); ?>
    </p>
    <?php
}

function RPJP_field_parent( $args ) {
    $options = get_option('RPJP_options', array()); //récupère les options créées
    //crée un input texte pour y entrer le terme parent voulu
	?>
    <input  type="text"  
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
            name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="<?php echo isset( $options['RPJP_parent'] ) ?  $options['RPJP_parent'] : false; ?>">
    </input>
    <p class="description">
        <?php esc_html_e( 'Entrez le nom du terme mère qui permet de filtrer les pubs sur un même CPT.', 'rpjp-plugin' ); ?>
    </p>
    <?php
}

function RPJP_field_size($args){
	$options = get_option('RPJP_options', array()); //récupère les options créées
    //créer un input de nombre pour y entrer la taille pour laquelle le site passe en format mobile
	?>
    <input  type="number"  
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
            name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			placeholder="992"
			value="<?php echo isset( $options['RPJP_size'] ) ?  $options['RPJP_size'] : false; ?>">
    </input>
    <p class="description">
        <?php esc_html_e( 'Entrez une largeur en PX en dessous de laquelle s\'affichera la version mobile.', 'rpjp-plugin' ); ?>
    </p>
    <?php
}

function RPJP_field_prefixe($args){
	$options = get_option('RPJP_options', array()); //récupère les options créées
	//créer un input de texte pour y entrer le préfixe voulu pour la référence auto
	?>
    <input  type="text"  
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
            name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="<?php echo isset( $options['RPJP_prefixe'] ) ?  $options['RPJP_prefixe'] : false; ?>">
    </input>
    <p class="description">
        <?php esc_html_e( 'Entrez un préfixe pour la génération automatique des références.', 'rpjp-plugin' ); ?>
    </p>
    <?php
}

function RPJP_field_suffixe($args){
	$options = get_option('RPJP_options', array()); //récupère les options créées
	//créer un input de texte pour y entrer le suffixe voulu pour la référence auto
	?>
    <input  type="text"  
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['RPJP_custom_data'] ); ?>"
            name="RPJP_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="<?php echo isset( $options['RPJP_suffixe'] ) ?  $options['RPJP_suffixe'] : false; ?>">
    </input>
    <p class="description">
        <?php esc_html_e( 'Entrez un suffixe pour la génération automatique des références.', 'rpjp-plugin' ); ?>
    </p>
    <?php
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
