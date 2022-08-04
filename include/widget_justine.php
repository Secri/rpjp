<?php

class RPJP_widget extends WP_Widget {
	/*Construction d'un nouveau widget*/
	function __construct() {
		parent::__construct(
			'RPJP_widget', //id
			__('Widget publicitaire', ' RPJP_widget_domain'), //nom 
			array( 'description' => __( 'Permet l\'ajout de publicités avec le custom post-type "régie publicitaire"', 'RPJP_widget_domain' ), ) //description
		);
	}

	/*Gestion de l'affichage en front*/
	public function widget( $args, $instance ) {
		
        if ( ! isset( $args['widget_id'] ) ) {
                $args['widget_id'] = $this->id;
        }

	global $wp_query; //on fait de $wp_query une variable globale
	$idP = $wp_query->post->ID; //on stocke l'id de la page où l'on se situe
	$options = get_option( 'RPJP_options', array() ); //on récupère les données de la page d'options

        //Filtre les pages en fonction du post-type et des paramètres donnés
        $all = new WP_Query( apply_filters( 'widget_posts_args', array(
                'post_type'           => 'regie_publicitaire',
                'no_found_rows'       => true,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => true
        ) ) );

		$date = date('y-m-d'); //récupération de la date du jour		
		//Loop permettant l'affichage des posts du post-type donné en paramètre
        if ($all->have_posts()) :
			if(isset($args['before_widget'])){
				echo $args['before_widget']; //Affiche le code avant de widget s'il y en a 
			}
			while ( $all->have_posts() ) : $all->the_post(); //tant qu'il y a des posts dans le post-type
				if(get_post_type($idP) == get_post_meta( get_the_ID(), 'cpt', true )){ //teste si le post-type de la page actuelle correspond au post-type passé en paramètre
					if(get_post_meta( get_the_ID(), 'categ', true ) != 'toutes' || null){ //teste si le champ possède une valeur autre que celle par défaut ou null
						$args = array( //tableau de conditions pour récupérer les bons posts
							'post_type'		=> get_post_meta( get_the_ID(), 'cpt', true ),
							'tax_query'		=> array( //récupère les posts avec le terme de la taxonomie passé en paramètre
								array(
									'taxonomy'	=> $options['RPJP_taxo'],
									'field'		=> 'slug',
									'terms'		=> get_post_meta( get_the_ID(), 'categ', true )
								),
							),
						);
						$p = get_posts($args); //récupère les posts correspondants aux paramètres
						foreach($p as $post){ //parcours tout ces posts
							echo '<div class="get_select" style="display:none">'.$options['RPJP_div'].'</div>';
							echo '<div class="get_size" style="display:none">'.$options['RPJP_size'].'</div>';
							if($idP == $post->ID){ //si l'id du post actuel correspond à un id de la liste
								//récupère le type de l'image pour l'affichage sur ordinateur
								$doc_idDesk = get_post_meta( get_the_ID(), 'image_desktop', true );
								$filenameDesk = basename( get_attached_file( $doc_idDesk ) );
								$filetypeDesk = wp_check_filetype($filenameDesk);
								//récupère le type de l'image pour l'affichage sur mobile
								$doc_idMob = get_post_meta( get_the_ID(), 'image_mobile', true );
								$filenameMob = basename( get_attached_file( $doc_idMob ) );
								$filetypeMob = wp_check_filetype($filenameMob);
								//vérifie si la date de fin n'est pas dépassée et si la date de début a été atteinte
								$expirationtime = get_post_custom_values('dateFin'); //récupère la date de fin
								$debut = get_post_custom_values('dateDeb'); //récupère la date de début
								if (is_array($expirationtime) || is_array($debut)) { 
									$expirestring = implode($expirationtime);
									$debstring = implode($debut);
								}
								$secondsbetween = strtotime($expirestring)-time();
								$deb = strtotime($debstring)-time();
								if ( $secondsbetween > 0 && $deb < 0 ) { //si la publicité est encore valide et a commencé
									echo '<div class="RPJP_mobile"><a class="lien" href='.get_post_meta( get_the_ID(), 'lien', true ).'>'; //récupère le lien passé en paramètre
									if(get_post_meta( get_the_ID(), 'follow', true ) == "on"){ //vérifie la valeur de la checkbox "No follow"
										if($filetypeDesk['type'] == 'image/gif') {
											echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true), 'Full Size').'" width ="100%" rel="nofollow"/>'; //Affiche l'image
										}else{
											echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true),'desktop').'" width ="100%" rel="nofollow"/>'; //Affiche l'image	
										}
										if(get_post_meta( get_the_ID(), 'mobile', true ) == "on"){
											wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile
											if($filetypeDesk['type'] == 'image/gif') {
												echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'Full Size').'" width ="100%" rel="nofollow"/>'; 
											}else{
												echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'mobile').'" width ="100%" rel="nofollow"/>'; 
											}
										}
									}else{
										if($filetypeDesk['type'] == 'image/gif') {
											echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true), 'Full Size').'" width ="100%" />'; //Affiche l'image
										}else{
											echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true),'desktop').'" width ="100%" />'; //Affiche l'image 
										}
										if(get_post_meta( get_the_ID(), 'mobile', true ) == "on"){
											wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile
											if($filetypeDesk['type'] == 'image/gif') {
												echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'Full Size').'" width ="100%"/>'; 
											}else{
												echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'mobile').'" width ="100%"/>'; 
											}
										}
									}
									//Bouton qui permet de fermer la publicité sur mobile
									echo '</a><svg class="svg" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin">
									<style>
										.svg{
											width:20px;
											height:20px;
											display:none;
											position : fixed;
											left: 5px;
										}
										@keyframes rotation {
											100% {
												transform: rotate(90deg);
											}
										}
										svg:hover {
											cursor:pointer;
											animation:rotation 1s forwards;
										}
										svg:hover g{
											fill:rgba(237,237,237,1)
										}
									</style>
									<g fill="rgba(237,237,237,0.8)">
										<path d="M11.414 10l2.829-2.828a1 1 0 1 0-1.415-1.415L10 8.586 7.172 5.757a1 1 0 0 0-1.415 1.415L8.586 10l-2.829 2.828a1 1 0 0 0 1.415 1.415L10 11.414l2.828 2.829a1 1 0 0 0 1.415-1.415L11.414 10zM10 20C4.477 20 0 15.523 0 10S4.477 0 10 0s10 4.477 10 10-4.477 10-10 10z" />
									</g>
									</svg></div>';
								}
							}		
						}					
					}else{ //publie sur toutes les pages du post-type
						//récupère le type de l'image pour l'affichage sur ordinateur
						$doc_idDesk = get_post_meta( get_the_ID(), 'image_desktop', true );
						$filenameDesk = basename( get_attached_file( $doc_idDesk ) );
						$filetypeDesk = wp_check_filetype($filenameDesk);
						//récupère le type de l'image pour l'affichage sur mobile
						$doc_idMob = get_post_meta( get_the_ID(), 'image_mobile', true );
						$filenameMob = basename( get_attached_file( $doc_idMob ) );
						$filetypeMob = wp_check_filetype($filenameMob);
						//vérifie si la date de fin n'est pas dépassée et si la date de début a été atteinte
						$expirationtime = get_post_custom_values('dateFin'); //récupère la date de fin
						$debut = get_post_custom_values('dateDeb'); //récupère la date de début
						if (is_array($expirationtime) || is_array($debut)) { 
							$expirestring = implode($expirationtime);
							$debstring = implode($debut);
						}
						$secondsbetween = strtotime($expirestring)-time();
						$deb = strtotime($debstring)-time();
						if ( $secondsbetween > 0 && $deb < 0 ) { //si la publicité est encore valide et a commencé
							echo '<div class="RPJP_mobile"><a class="lien" href='.get_post_meta( get_the_ID(), 'lien', true ).'>'; //récupère le lien passé en paramètre
							if(get_post_meta( get_the_ID(), 'follow', true ) == "on"){ //vérifie la valeur de la checkbox "No follow"
								if($filetypeDesk['type'] == 'image/gif') {
									echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true), 'Full Size').'" width ="100%" rel="nofollow"/>'; //Affiche l'image
								}else{
									echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true),'desktop').'" width ="100%" rel="nofollow"/>'; //Affiche l'image	
								}
								if(get_post_meta( get_the_ID(), 'mobile', true ) == "on"){
									wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile
									if($filetypeDesk['type'] == 'image/gif') {
										echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'Full Size').'" width ="100%" rel="nofollow"/>'; 
									}else{
										echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'mobile').'" width ="100%" rel="nofollow"/>'; 
									}
								}
							}else{
								if($filetypeDesk['type'] == 'image/gif') {
									echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true), 'Full Size').'" width ="100%" />'; //Affiche l'image
								}else{
									echo '<img class="imageDesktop" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_desktop', true),'desktop').'" width ="100%" />'; //Affiche l'image 
								}
								if(get_post_meta( get_the_ID(), 'mobile', true ) == "on"){
									wp_enqueue_script( 'RPJP-admin-mobile', plugins_url( 'js/mobile.js', __FILE__), '', '', true ); //ajout du script pour gérer l'affichage mobile
									if($filetypeDesk['type'] == 'image/gif') {
										echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'Full Size').'" width ="100%"/>'; 
									}else{
										echo '<img class="imageMobile" src="'.wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'image_mobile', true),'mobile').'" width ="100%"/>'; 
									}
								}
							}
							//Bouton qui permet de fermer la publicité sur mobile
							echo '</a><svg class="svg" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin">
							<style>
								.svg{
									width:20px;
									height:20px;
									display:none;
									position : fixed;
									left: 95%;
								}
								@keyframes rotation {
									100% {
										transform: rotate(90deg);
									}
								}
								svg:hover {
									cursor:pointer;
									animation:rotation 1s forwards;
								}
								svg:hover g{
									fill:rgba(237,237,237,1)
								}
							</style>
							<g fill="rgba(237,237,237,0.5)">
								<path d="M11.414 10l2.829-2.828a1 1 0 1 0-1.415-1.415L10 8.586 7.172 5.757a1 1 0 0 0-1.415 1.415L8.586 10l-2.829 2.828a1 1 0 0 0 1.415 1.415L10 11.414l2.828 2.829a1 1 0 0 0 1.415-1.415L11.414 10zM10 20C4.477 20 0 15.523 0 10S4.477 0 10 0s10 4.477 10 10-4.477 10-10 10z" />
							</g>
							</svg></div>';
						}
					}
				}
            endwhile; 
			if(isset($args['after_widget'])){
				echo $args['after_widget']; //Affiche le code après de widget s'il y en a 
			}
            wp_reset_postdata(); // Reset la variable globale $the_post 
        endif; 
	}
	
	/*Gestion de l'affichage en back*/
	public function form( $instance ) {
        echo '<br>Ce widget est automatique et ne nécessite pas de paramètres.';
	}
	
	/*Mise à jour en cas de modifications dans le back*/
	public function update( $new_instance, $old_instance ) {
        //pas de paramètres donc pas d'update.
	}
}

/*Enregistrement du widget sur le site*/
add_action( 'widgets_init', 'RPJP_register_widget' );
	
function RPJP_register_widget() {
	register_widget( 'RPJP_widget' );
}
