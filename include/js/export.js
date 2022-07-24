function end_form(){
	var champ_obligatoire = [ 'debut', 'fin' ];
	var champ_plein = true;
	for (var h=0; h<2; h++){
		valeur = document.getElementById(champ_obligatoire[h]).value;
		if( (valeur.length == 0) || (valeur == "") || (valeur == "null") ){
			champ_plein = false;
		}
	}
	 
	if (champ_plein){
		document.getElementById('exporter').disabled = false;
	}else{
		document.getElementById('exporter').disabled = true;
	}
}

end_form();