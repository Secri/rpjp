// On crée un écouteur qui va bloquer la saisie au clavier sur les input type date
document.querySelector('#dateDeb').addEventListener('keypress', noKeyPress); 
document.querySelector('#dateFin').addEventListener('keypress', noKeyPress);

	function noKeyPress(e) {
		e.returnValue = false;
	}

document.querySelector('#dateDeb').onchange = date_deb_valide; //Création d'un écouteur sur le changement de valeur de la date de début
document.querySelector('#dateFin').onchange = date_deb_valide; //Création d'un écouteur sur le changement de valeur de la date de fin

		function date_deb_valide(e){ //Controleur de l'écouteur
			let currentYear = new Date().getFullYear(); //On récupére l'année courante
			if (new Date(this.value).getFullYear().toString().length > 3 && new Date(this.value).getFullYear() < currentYear) { //On vérifie que l'année n'est pas inférieure à l'année de la date courante
				//window.alert("Vous ne pouvez pas sélectionner une année antérieure à " + currentYear + '.');
				Swal.fire({ //Fenêtre d'alerte Sweet Alert 2
					icon: 'error',
					title: 'Erreur',
					text: 'Vous ne pouvez pas sélectionner une date dont l\'année est antérieure à ' + currentYear + '.',
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
							title: 'Erreur',
							text: 'La date de début est postérieure à la date de fin.',
							allowOutsideClick: false
						});
						this.value = ""; //On remet la valeur de l'input à 0
					}
				}
			}
		}