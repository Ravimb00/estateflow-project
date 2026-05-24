function openModal(){

document.getElementById(
'loginModal'
).style.display = 'flex';

}

function closeModal(){

document.getElementById(
'loginModal'
).style.display = 'none';

}

/* USER TAB */

function showUser(){

document.getElementById(
'userLogin'
).style.display = 'block';

document.getElementById(
'managerLogin'
).style.display = 'none';

document.getElementById(
'userTab'
).classList.add('active');

document.getElementById(
'managerTab'
).classList.remove('active');

}

/* MANAGEMENT TAB */

function showManager(){

document.getElementById(
'userLogin'
).style.display = 'none';

document.getElementById(
'managerLogin'
).style.display = 'block';

document.getElementById(
'managerTab'
).classList.add('active');

document.getElementById(
'userTab'
).classList.remove('active');

}

/* CLOSE MODAL OUTSIDE CLICK */

window.onclick = function(event){

let modal =
document.getElementById(
'loginModal'
);

if(event.target == modal){

modal.style.display = 'none';

}

}

/* COOKIES */

function acceptCookies(){

let cookieBox =
document.getElementById(
'cookieBox'
);

if(cookieBox){

cookieBox.style.display = 'none';

}

}

/* PAGE LOAD */

document.addEventListener(
'DOMContentLoaded',
function(){

console.log(
'EstateFlow Loaded'
);

}
);