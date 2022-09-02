var regenBtn = document.getElementById('rpjp_regen_btn');

regenBtn.addEventListener('click', rpjp_validate_regen, false);

function rpjp_validate_regen(e) {
	e.preventDefault();
	Swal.fire({
		icon: 'warning',
		title: 'Attention',
		html: 'Voulez-vous vraiment regénérer toutes les références ?<br>Cette action sera <b>IRR&Eacute;VERSIBLE</b>.',
		showCancelButton: true,
		cancelButtonText: 'Annuler',
		cancelButtonColor: '#d33',
		allowOutsideClick: false
	}).then((result) => {
		if (result.isConfirmed) {
			document.getElementById('posts-filter').submit();
		}
	})
}

// CSS Fix pagination
document.querySelector('.tablenav-pages').style.margin = '9px 0';
