let er = document.querySelector('#setting-error-RPJP_err');
er.style.display = "none";
let regex = /^#{1}[a-zA-Z0-9_\-]+$|^\.{1}[a-zA-Z0-9_\-]+(\s?\.{1}[a-zA-Z0-9_\-]+)*$/i;
let div = document.querySelector('#RPJP_div').value;
console.log(regex.test(div));
if(!regex.test(div)) {
	er.style.display = "block";
}