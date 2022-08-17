<?php

/*On utilise une fonction pour créer notre custom post type*/
add_action( 'init', 'RPJP_custom_post_type', 0 );

function RPJP_custom_post_type() {

	// On rentre les différentes dénominations de notre custom post type qui seront affichées dans l'administration
	$labels = array(
		//Le nom 
		'name'                => _x( 'Régie publicitaire', 'Post Type General Name'),
		//Le nom au singulier
		'singular_name'       => _x( 'Publicité', 'Post Type Singular Name'),
		//Le libellé affiché dans le menu
		'menu_name'           => __( 'Régie publicitaire'),
		//Les différents libellés de l'administration
		'all_items'           => __( 'Toutes les publicités'),
		'view_item'           => __( 'Voir les publicités'),
		'add_new_item'        => __( 'Ajouter une nouvelle publicité'),
		'add_new'             => __( 'Ajouter'),
		'edit_item'           => __( 'Editer la publicité'),
		'update_item'         => __( 'Modifier la publicité'),
		'search_items'        => __( 'Rechercher une publicité'),
		'not_found'           => __( 'Non trouvée'),
		'not_found_in_trash'  => __( 'Non trouvée dans la corbeille'),
	);
	
	//On peut définir ici d'autres options pour notre custom post type
	
	$args = array(
		'label'               => __( 'Régie publicitaire'),
		'description'         => __( 'Gestion des publicités du site'),
		'labels'              => $labels,
		//On définit les options disponibles dans l'éditeur de notre custom post type
		'supports'            => array( 'title','thumbnail' ),
		//Différentes options supplémentaires
		'show_in_rest' 		  => true,
		'hierarchical'        => false,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui' 			  => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'	  => false,
		'menu_icon' 		  => 'dashicons-megaphone',
		'as_archive'		  => false,
		'rewrite'			  => false,
		'has_archive'         => true,
		'rewrite'			  => array( 'slug' => 'publicite'),

	);
	
	// On enregistre notre custom post type qu'on nomme ici "regie_publicitaire" et ses arguments
	register_post_type( 'regie_publicitaire', $args );
}

/*Ajout de la page de réglages et d'export*/
include plugin_dir_path( __FILE__ ) . './settings.php';
include plugin_dir_path( __FILE__ ) . './export.php';

/* Ajout du cron job qui dépublie les publicités dépassées */
include plugin_dir_path( __FILE__ ) . './unpublish_cron.php';

/*Ajout de metabox pour avoir des champs personnalisés lors de la création ou l'édition d'une publicité*/
add_action( 'admin_menu', 'RPJP_add_metabox' );

function RPJP_add_metabox() {
	
	//Box pour ajouter l'image au format pour mobile
	add_meta_box(
		'RPJP_metabox_imgmobile', // id metabox 
		'Images', // titre
		'RPJP_image_mobile_callback', // fonction de callback 
		'regie_publicitaire', // post type 
		'normal', // position 
		'default'); // priorité

	//Box pour gérer les paramètres de la publicité (post-type d'affichage, lien, no follow, dates...)
	add_meta_box(
		'RPJP_metabox', // id metabox 
		'Informations sur la publicité', // titre
		'RPJP_metabox_callback', // fonction de callback 
		'regie_publicitaire', // post type 
		'normal', // position 
		'default' // priorité
	);
	
	//Box permetant d'indiquer le statut de la publicité (brouillon, en production, publiée, dépassée...)
	add_meta_box(
		'RPJP_metabox_statut', // id metabox 
		'Statut', // titre
		'RPJP_statut_callback', // fonction de callback 
		'regie_publicitaire', // post type 
		'side', // position 
		'default' // priorité
	);
	
	//Box permetant d'afficher la référence de la publicité
	add_meta_box(
		'RPJP_metabox_ref', // id metabox 
		'Référence', // titre
		'RPJP_ref_callback', // fonction de callback 
		'regie_publicitaire', // post type 
		'side', // position 
		'default' // priorité
	);
}

/*Fonctions qui gèrent l'affichage du contenu des metabox*/
function RPJP_image_mobile_callback($post){ //images
	include plugin_dir_path( __FILE__ ) . './images.php';
}

function RPJP_metabox_callback( $post ) { //paramètres
	include plugin_dir_path( __FILE__ ) . './form.php';
}

function RPJP_statut_callback($post){ //statut
	?>
	<!-- Affiche une liste déroulante qui indique le statut de la publicité -->
	<p class="meta-options field">
    		<select id="statut" name="statut" disabled>
			<option value="Programmée" <?php if ( handleStatus($post) == 'Programmée' ) { echo "selected";} ?>>Programmée</option>
			<option value="Brouillon" <?php if ( handleStatus($post) == 'Brouillon' ) { echo "selected";} ?>>Brouillon</option>
			<option value="Publiée" <?php if ( handleStatus($post) == 'Publiée' ) { echo "selected";} ?>>Publiée</option>
			<option value="Dépassée" <?php if ( handleStatus($post) == 'Dépassée' ) { echo "selected";} ?>>Dépassée</option>
			<option value="Erreur" <?php if ( handleStatus($post) == 'Erreur' ) { echo "selected";} ?> >Erreur</option>
		</select>
	</p>
	<?php
}

function RPJP_ref_callback($post){ //référence des publicités
	?>
	<p class="meta-options field">
		<input disabled id="ref" type="text" name="ref"
		       value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'ref', true ) ); ?>" >
	</p>
	<?php
}

/*Fonction permettant de sauvegarder le contenu des metabox*/
add_action( 'save_post', 'RPJP_save_meta_boxes',1 );
 
function RPJP_save_meta_boxes( $post_id ) {
    
	//Création de la ref à l'enregistrement du post
	if ( isset( $_POST['publish'] ) ) {
		$options = get_option('RPJP_options', array()); 
		$fullPrefixe = $options['RPJP_prefixe'] != '' ? $options['RPJP_prefixe'] . '-' : '';
		$fullSuffixe = $options['RPJP_suffixe'] != '' ? '-' . $options['RPJP_suffixe'] : '';
		update_post_meta( $post_id, 'ref', sanitize_text_field( strtoupper($fullPrefixe) . get_the_date('mY') . "-" . get_the_ID(). strtoupper($fullSuffixe) ) );
	}
		
	//Sauvegarde l'image ajoutée par la version mobile
	if ( ! current_user_can( 'edit_posts', $post_id ) ){ return 'not permitted'; }
    $meta_keys = array('image_desktop','image_mobile');
    foreach($meta_keys as $meta_key){
		if (isset($_POST['action']) && $_POST['action'] == 'editpost') { //Ne s'active pas lorsqu'on trash le post !
			if(isset($_POST[$meta_key]) && intval($_POST[$meta_key])!=''){
				update_post_meta( $post_id, $meta_key, intval($_POST[$meta_key]));
			}else{
				update_post_meta( $post_id, $meta_key, '');
			}
		}
	}
	
	//Sauvegarde le champ de choix du post-type
	if(isset($_POST['cpt'])){
		$cpt = $_POST['cpt']; 
		update_post_meta($post_id, 'cpt', $cpt,sanitize_text_field( $_POST[$cpt] ));
	}
	//Sauvegarde le champ de choix de la catégorie
	if(isset($_POST['categ'])){
		$categ = $_POST['categ']; 
		update_post_meta($post_id, 'categ', $categ,sanitize_text_field( $_POST[$categ] ));
	}
	//Sauvegarde l'état de la checkbox "follow"
	if(isset($_POST['follow'])){ // Si la case est cochée à la soumission du formulaire
		update_post_meta($post_id, "follow", $_POST["follow"]);
	} else {
		update_post_meta( $post_id, 'follow', 'off' );
	}
	//Sauvegarde l'état de la checkbox "mobile"
	if(isset($_POST['mobile'])){ // Si la case est cochée à la soumission du formulaire
		update_post_meta($post_id, "mobile", $_POST["mobile"]);
	} else {
		update_post_meta( $post_id, 'mobile', 'off');
	}
	
	//Sauvegarde les données des champs de texte et calendrier
    $fields = [
        'lien',
        'dateDeb',
		'dateFin',
    ];
    foreach ( $fields as $field ) {
        if ( array_key_exists( $field, $_POST ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
    }
}

add_action('do_meta_boxes', 'remove_thumbnail_box');
/* on enlève la thumbnail box de la barre latérale */
function remove_thumbnail_box() {
    remove_meta_box( 'postimagediv','regie_publicitaire','side' );
}

add_action( 'save_post', 'RPJP_verif');
/*Fonction qui permet de vérifier si la date de début entrée n'est pas postérieure à la date de fin*/
function RPJP_verif($post_id){
	//l'action ne s'exécute pas si l'on met le poste à la corbeille ou si on le restaure
    if(
        isset($_REQUEST['action']) &&
        ( $_REQUEST['action'] == 'trash' || $_REQUEST['action'] == 'untrash')
    ){
        return;
    }
	$debut = strtotime(get_post_meta( $post_id, 'dateDeb', true )); //récupère la date de début
	$fin = strtotime(get_post_meta( $post_id, 'dateFin', true )); //récupère la date de fin
	if($debut > $fin){ //si la date de début est postérieure
		remove_action('save_post', 'RPJP_verif'); //on enlève la fonction du hook pour éviter les boucles infinies
		//on entre les paramètres qui passeront le post actuel en brouillon
		$my_args = array(
			'ID' => $post_id,
			'post_status' => 'draft',
		);
		wp_update_post( $my_args ); //on passe le poste en brouillon
		add_action('save_post', 'RPJP_verif'); //on remet la fonction dans le hook
	}
}

add_action('post_updated_messages','RPJP_show_error',1000);
/*Fonction qui affiche l'erreur en cas de dates invalides*/
function RPJP_show_error(){
	$debut = strtotime(get_post_meta( get_the_ID(), 'dateDeb', true )); //récupère la date de début
	$fin = strtotime(get_post_meta( get_the_ID(), 'dateFin', true )); //récupère la date de fin
	if($debut > $fin){ //si la date de début est postérieure
		?>
		<div class="notice notice-error" >
			<p><strong><?php _e( 'La date de début ne peut pas être postérieure à la date de fin.', 'RPJP' ); ?></strong></p>
		</div>
		<?php	
	}
}

add_action('init','RPJP_session'); // Vérifie si la session a débuté et la lance si ce n'est pas le cas
function RPJP_session(){
	if ( !session_id() ) {
		session_start();
	}
}

add_action( 'save_post', 'RPJP_dates_disponibles', 1001);
/*Fonction qui vérifie que la publicité à publier n'est pas prévue en même temps qu'une autre sur les mêmes post-type et catégorie*/
function RPJP_dates_disponibles($post_id){
	//l'action ne s'exécute pas si l'on met le poste à la corbeille ou si on le restaure
   if(
        isset($_REQUEST['action']) && ( $_REQUEST['action'] == 'trash' )
    ){
        return;
    }
	$debut = get_post_meta($post_id, 'dateDeb', true ); //récupère la date de début
	$fin = get_post_meta($post_id, 'dateFin', true ); //récupère la date de fin
	$cpt = get_post_meta( $post_id, 'cpt', true ); //récupère le post-type d'affichage choisi
	$categ = get_post_meta( $post_id, 'categ', true ); //récupère la catégorie choisie
	//paramètres pour l'appelle de wp_query: on récupère les posts ayant le même post-type et la même catégorie en paramètre et dont les dates se superposeraient
	$args = array(
    'post_type'  => 'regie_publicitaire',
	'post_status' => 'publish',
	'posts_per_page' => -1,
    'meta_query' => array(
			'relation'	=> 'AND',
			array(
				'key'     => 'cpt',
				'value'   => $cpt,
				'type'	  => 'char',
				'compare' => 'LIKE',
			),
			array(
				'key'     => 'categ',
				'value'   => $categ,
				'type'	  => 'char',
				'compare' => 'LIKE',
			),	
			array(
				'relation'	=> 'OR',
				
				array( // La période sélectionnée est englobée par une pub avec les mêmes caractéristiques (relation AND par défaut)
					array(
						'key'     => 'dateDeb',
						'value'   => $debut,
						'type'    => 'DATE',
						'compare' => '<='
					),
					array(
						'key'     => 'dateFin',
						'value'   => $fin,
						'type'    => 'DATE',
						'compare' => '>='
					),
				),
				array( // la date de début d'une pub existante se trouve entre $debut et $fin (intersection)
					'key'     => 'dateDeb',
					'value'   => array($debut, $fin),
					'type'    => 'DATE',
					'compare' => 'BETWEEN'
				),
				array( // la date de fin d'une pub existante se trouve entre $debut et $fin (intersection)
					'key'     => 'dateFin',
					'value'   => array($debut, $fin),
					'type'    => 'DATE',
					'compare' => 'BETWEEN'
				),
			),
		),
	);
	$query = new WP_Query( $args );	//on fait la requête
	if ( $query->post_count > 1 ) { //si la requête retourne plus d'un post 
				
		while( $query->have_posts() ) : $query->the_post(); // On parcourt les 2 pubs qui ont été retournées
			get_the_title($post_id) != get_the_title(get_the_ID()) ? $titre = get_the_title(get_the_ID()) : false ; // On cible celle qui existait au préalable et on stocke son titre
		endwhile;
		
		remove_action('save_post', 'RPJP_dates_disponibles',1001); //on enlève la fonction du hook pour éviter les boucles infinies 
		//on entre les paramètres qui passeront le post actuel en brouillon
		$arg = array(
			'ID' => $post_id,
			'post_status' => 'draft',
		);
		wp_update_post( $arg ); //on passe le poste en brouillon
		add_action('save_post', 'RPJP_dates_disponibles',1001); //on remet la fonction dans le hook	
		set_transient( "rpjp_save_post_error", "Impossible de publier cette publicité car la période du <strong>". date( 'd-m-Y', strtotime( get_post_meta($post_id, 'dateDeb', true) ) ) ."</strong> au <strong>". date('d-m-Y', strtotime( get_post_meta($post_id, 'dateFin', true) ) ) ."</strong> est en conflit avec la publicité \"<strong>".$titre."</strong>\""." sur les contenus de type \"<strong>". get_post_meta($post_id, 'cpt', true) ."\"</strong> présentants la catégorie \"<strong>".get_post_meta($post_id, 'categ', true)."\"</strong>.", 60 );
		$_SESSION['id'] = $post_id;
	}else{
		delete_transient( "rpjp_save_post_error" );
		unset( $_SESSION['id'] );
	}
	wp_reset_postdata(); //on reset les données du query
}

add_action('post_updated_messages','RPJP_show_error_dates_dispo',1002);
/*Fonction qui affiche l'erreur si la publicité à publier est prévue en même temps qu'une autre sur les mêmes post-type et catégorie*/
function RPJP_show_error_dates_dispo(){
	if(get_post_type() == 'regie_publicitaire'){
		if(isset($_SESSION['id'])){
			if(get_the_ID() == $_SESSION['id']){
				if ($msg = get_transient( "rpjp_save_post_error" )){
					?><div class="notice notice-error">
						<p><?php echo $msg; ?></p>
					</div><?php
				}
			}
		}
	}
}

/* Fonction qui gère le statut des posts de la régie */
function handleStatus ($currentPost) {
	$startingTime   = strtotime( $currentPost->dateDeb ) - time();
	$expirationTime = strtotime( $currentPost->dateFin ) - time();
	
	if ( $currentPost->post_status == 'trash' ) {
		return __('Corbeille', 'RPJP_status');
	}
		
	else if ( $currentPost->post_status == 'auto-draft' || ( $currentPost->post_status == 'draft' && $expirationTime > 0 && $expirationTime > $startingTime ) ) {
		return __('Brouillon', 'RPJP_status');
	}
		
	else if ( $currentPost->post_status == 'publish' && $startingTime > 0 && $expirationTime > $startingTime ) {
		return __('Programmée', 'RPJP_status');
	}
	
	else if ( $currentPost->post_status == 'publish' && $expirationTime > 0 && $expirationTime > $startingTime ) {
		return __('Publiée', 'RPJP_status');
	}
	
	else if ( ($currentPost->post_status == 'publish' && $expirationTime < 0 && $expirationTime > $startingTime) || ($currentPost->post_status == 'draft' && $expirationTime < 0 && $expirationTime > $startingTime) ) {
		return __('Dépassée', 'RPJP_status');
	}
	
	else { return __('Erreur', 'RPJP_status'); }
}

/* REGENERATE REFS */
	/*Enregistre le bouton sur le hook voulu (page qui liste toutes les pubs)*/
	add_action( 'manage_posts_extra_tablenav', 'RPJP_regen_refs_button', 20, 1 );

	/*Ajout de boutons sur la page listant toutes les publicités*/
	function RPJP_regen_refs_button( $which ) {
		
		global $typenow;
	  
		if ( 'regie_publicitaire' === $typenow && 'top' === $which ) { //Si on se trouve sur la liste de type regie_publicitaire
			wp_enqueue_script( 'rpjp-confirm-regen', plugins_url( '/js/regen.js', __FILE__), '', '', true );
			?>
			<div class="alignleft actions" style="margin-left:12px;padding-left:20px;border-left:1px solid">
				<form method="post">
					
					<input type='hidden' name="rpjp_regen_refs" value="rpjp_regen_refs" /> <!-- C'est cet input qui va envoyer les infos au moment du submit en JS -->
					<input type="submit" id="rpjp_regen_btn" class="button button-secondary" value="<?php _e('Regénérer les références', 'rpjp_regen'); ?>" />
				
				</form>
			</div>
			<?php
		}
	}
	add_action( 'init', 'rpjp_regenerate_refs' );
	/* Fonction qui regénère les références de toutes les pubs */
	function rpjp_regenerate_refs() {
		if ( isset ($_POST['rpjp_regen_refs']) ) { // Récupère les infos du formulaire ci-dessus
			global $post;
			$arg = array(
				'post_type' => 'regie_publicitaire',
				'post_status' => array('publish', 'draft'),
				'posts_per_page' => -1,
			);
			$arr_post = get_posts($arg);
			$options = get_option('RPJP_options', array()); 
			foreach($arr_post as $post){
					$initialCrea = explode( '-', $post->ref );
					$fullPrefixe = $options['RPJP_prefixe'] != '' ? $options['RPJP_prefixe'] . '-' : '';
					$fullSuffixe = $options['RPJP_suffixe'] != '' ? '-' . $options['RPJP_suffixe'] : '';
					update_post_meta( $post->ID, 'ref', sanitize_text_field( strtoupper($fullPrefixe) . date( 'mY', strtotime($post->post_date) ) . "-" . get_the_ID(). strtoupper($fullSuffixe) ) );
			}
		}
	}

/* Charger la librairie swal2*/
add_action( 'admin_enqueue_scripts', 'load_swal_2' );
function load_swal_2(){
  wp_enqueue_script( 'swal2', plugin_dir_url( __FILE__ ) . 'js/sweetalert2.all.min.js', array(), true);
  //wp_enqueue_script( 'swal2', '//cdn.jsdelivr.net/npm/sweetalert2@11', array(), true); Possibilité de charger la librairie depuis un CDN
}
