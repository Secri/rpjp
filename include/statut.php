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