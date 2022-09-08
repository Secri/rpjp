// On crée un écouteur qui va bloquer la saisie au clavier sur les input type date
document.querySelector('#dateDeb').addEventListener('keypress', noKeyPress); 
document.querySelector('#dateFin').addEventListener('keypress', noKeyPress);

	function noKeyPress(e) {
		e.returnValue = false;
	}

document.querySelector('#dateDeb').onchange = date_deb_valide; //Création d'un écouteur sur le changement de valeur de la date de début
document.querySelector('#dateFin').onchange = date_deb_valide; //Création d'un écouteur sur le changement de valeur de la date de fin

	// Gestion du bouton exporter de la liste des posts regie_publicitaire
	if ( document.body.contains(document.getElementById('exporter')) ) {
		document.getElementById('exporter').disabled = true;
	}

		function date_deb_valide(e){ //Controleur de l'écouteur
			let currentYear = new Date().getFullYear(); //On récupére l'année courante
			if (new Date(this.value).getFullYear().toString().length > 3 && new Date(this.value).getFullYear() < currentYear) { //On vérifie que l'année n'est pas inférieure à l'année de la date courante
				//window.alert("Vous ne pouvez pas sélectionner une année antérieure à " + currentYear + '.');
				Swal.fire({ //Fenêtre d'alerte Sweet Alert 2
					icon: 'error',
					title: rpjp_check_dates_vars.error_title, //Récupération du string depuis wp_localize_script()
					text: rpjp_check_dates_vars.date_ante + currentYear + '.', //Récupération du string depuis wp_localize_script()
					allowOutsideClick: false
				});
				this.value = ""; //On remet la valeur de l'input à 0
			}
			if (document.querySelector('#dateDeb').value !== '' && document.querySelector('#dateFin').value !== '') { // Si les deux input sont renseignés
				let dateDeb = new Date(document.querySelector('#dateDeb').value); //on construit une nouvelle date de début à partir de la valeur du champ
				let dateFin = new Date(document.querySelector('#dateFin').value); //on construit une nouvelle date de fin à partir de la valeur du champ
				if (dateDeb.getFullYear() >= currentYear && dateFin.getFullYear() >= currentYear) { //Si l'année saisie est > ou = à l'année courante
					if(dateDeb > dateFin){ //Si la date de début est postérieure à la date de fin
						//window.alert("La date de début est postérieure à la date de fin, réessayez.");
						Swal.fire({ //Fenêtre d'alerte Sweet Alert 2
							icon: 'error',
							title: rpjp_check_dates_vars.error_title, //Récupération du string depuis wp_localize_script()
							text: rpjp_check_dates_vars.date_post, //Récupération du string depuis wp_localize_script()
							allowOutsideClick: false
						});
						if (this.id === 'dateDeb') {
							this.value = "";
							document.getElementById('dateFin').value = '';
						} else {
							this.value = "";
						}
					}
				}
			}
			if ( document.body.contains(document.getElementById('exporter')) ) { // Si l'élément HTML ID 'exporter' existe
				let isDebFilled = document.querySelector('#dateDeb').value; // On récupère la valeur de début
				let isEndFilled = document.querySelector('#dateFin').value; // On récupére la valeur de fin
				if (isDebFilled.length > 0 && isEndFilled.length > 0) { // Si les deux dates sont renseignées
					document.getElementById('exporter').disabled = false; // On active le bouton exporter
				}
			}
		}