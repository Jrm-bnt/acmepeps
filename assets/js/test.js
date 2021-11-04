"use strict";

function test() {
	console.log(autocomplete.value);
	url = `/test/autocomplete/${autocomplete.value}`;
	fetch(url)
		.then(response => response.json())
		.then(products => {
			console.log(products);
			//* supprimer le contenu de la DIV englobante
			autocomplete_results.innerHTML = '';
			//* créer une DIV englobante.
			let div = document.createElement('div');

			//* Si aucun résultat, l'afficher.
			if (autocomplete.value && !products.length) {
				//* creer un P.
				let p = document.createElement('p');
				p.textContent = "Aucun résultat.";
				//* Ajouter le P en enfant de la div.
				div.appendChild(p)
				//* Ajouter la sous DIV en enfant de la div englobante.
				autocomplete_results.appendChild(p);
			}


			//* Pour chaque produit...
			products.forEach(product => {
				//* Créer un P 
				let p = document.createElement('p');
				//* Définir son contenu.
				p.textContent = `${product.name} (${product.ref}) ${product.price} €`;
				//* Ajouter le P en enfant de la div.
				div.appendChild(p)
				//* Ajouter la sous DIV en enfant de la div englobante.
				autocomplete_results.appendChild(p);
			});
		});
}

function email() {
	url = `/test/email`;
	fetch(url);
}

function selectCategory() {
	console.log(categories);
}