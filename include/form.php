<?php
	global $post; //Variable globale du post courant
	$custom = get_post_custom($post->ID); //On récupère les valeurs custom de l'objet post courant dans un tableau
?>
<div class="infos_box">
 		
	<!-- Liste déroulante affichant tous les post-type du site -->
	<p class="meta-options field">
        <label for="cpt"><?php _e('Page d\'affichage', 'rpjp-plugin'); ?><strong style="color:red">*</strong></label>
        <select id="cpt" name="cpt" required>
			<option value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'cpt', true ) ); ?>">-- <?php echo esc_attr( get_post_meta( get_the_ID(), 'cpt', true ) ); ?> --</option>
			<?php 
				$args = array( 'public'   => true );
				$cpt = get_post_types($args);
				foreach($cpt as $key => $value){
					if ($value != get_post_meta( get_the_ID(), 'cpt', true ) && $value != 'attachment') {
						echo '<option value='.$value.'>'.$value.'</option>';
					}
				}
			?>
		</select>
    </p>
	
	<!-- Liste déroulante affichant les catégories disponibles pour le post-type choisi -->
	<p class="meta-options field">
        <label for="categ"><?php _e('Catégorie d\'affichage', 'rpjp-plugin'); ?><strong style="color:red">*</strong></label>
        
        <select id="categ" name="categ" required>
			<option value="toutes"><?php _e('Afficher sur toutes les pages', 'rpjp-plugin'); ?></option>
			<?php	
				$categ = get_post_meta( get_the_ID(), 'categ', true );
				$options = get_option( 'RPJP_options', array() ); //on récupère les données de la page d'options
				
				//on récupère tous les termes parents
				$term = get_terms( array(
										  'taxonomy'   => $options['RPJP_taxo'],
										  'hide_empty' => false,
										  'parent'     => 0,
								  )  );
				
				//pour chaque terme parent, on cherche si son slug correspond à celui donné dans les paramètres
				foreach($term as $t){
					if($t->slug == $options['RPJP_parent']){
						$id = $t->term_id;
					}
				}
				
				$term_id = $id; //on récupère l'id obtenu après le parcours
				$taxonomy_name = $options['RPJP_taxo']; //on récupère le nom de la taxonomie
				$termchildren = get_term_children( $term_id, $taxonomy_name ); //on récupère la liste des enfants du terme parent
				
				//Si la taxonomie est définie, on affiche tout les enfants dans une liste déroulante
				if ($options['RPJP_taxo'] != "") {
					foreach ( $termchildren as $child ) {
						$term = get_term_by( 'id', $child, $taxonomy_name );
						($categ == $term->slug) ? $selected = " selected " : $selected = "";
						echo '<option value='.$term->slug.$selected.'>'.$term->name.'</option>';
					}
				}
			?>
		</select>
    </p>
	
	<!-- Zone de texte où définir le lien de redirection de la publicité -->
    <p class="meta-options field">
        <label for="lien"><?php _e('Lien de redirection', 'rpjp-plugin'); ?><strong style="color:red">*</strong></label>
        <input id="lien"
               type="text"
               name="lien"
			   required
               value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'lien', true ) ); ?>"
		>
    </p>
	
	<!-- Checkbox pour demander l'option "NoFollow" dans le lien -->
	<p class="meta-options field"> 
	<label for="follow"><?php _e('Nofollow', 'rpjp-plugin'); ?></label>
		<input type="checkbox" name="follow" <?php if(isset( $custom["follow"][0] ) && $custom["follow"][0] == 'on' ) { ?> checked="checked" <?php } ?> />
	</p>
	
	<!-- Calendrier pour saisir les dates de début et de fin -->
	<?php 
		//On charge le contrôle JS des dates et la librairie sweet alert
		wp_enqueue_script( 'rpjp-check-dates', plugins_url( '/js/date.js', __FILE__), '', '', true );	
	?> 
    <p class="meta-options field">
        <label for="dateDeb"><?php _e('Date de début', 'rpjp-plugin'); ?><strong style="color:red">*</strong></label>
        <input id="dateDeb"
               type="date"
               name="dateDeb"
			   required
               value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'dateDeb', true ) ); ?>"
		>
    </p>
	<p class="meta-options field">
        <label for="dateFin"><?php _e('Date de fin', 'rpjp-plugin'); ?><strong style="color:red">*</strong></label>
        <input id="dateFin"
               type="date"
               name="dateFin"
			   required
               value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'dateFin', true ) ); ?>"
		>
    </p>
	
	<!-- Ajout d'une checkbox pour activer ou désactiver la mise en page de pub sur mobile -->
	<p class="meta-options field"> 
		<label for="mobile">
			<?php 
				_e('Pub sur mobile', 'rpjp-plugin');
				if ( isset ($options['RPJP_size']) && $options['RPJP_size'] == "" ) {
					_e('&nbsp;(Pour activer cette option veuillez entrer un sélecteur CSS valide dans les Réglages)', 'rpjp-plugin');
				}
			?>
		</label>
		<input <?php if ( isset ($options['RPJP_size']) && $options['RPJP_size'] == "" ) echo 'disabled' ?> type="checkbox" name="mobile" <?php if( isset($custom["mobile"][0]) && $custom["mobile"][0] == 'on' ) { ?>checked="checked"<?php } ?> />
    </p>
</div>
