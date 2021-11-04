"use strict";

//* Quand changement d'option dans le SELECT des catégories...
idCategory.addEventListener('change', () => {
	//* Supprimmer les éventuelles options du SELECT des produits.
	idProduct.innerHTML = '';

	if (idCategory.value === '0')
		return selectProducts.style.visibility = 'hidden';


	//* A partir du tableau des catégories...
	categories
		//* Filtrer les catégories pour ne conserver que celle sélectionnée.
		.filter(category => category.idCategory === parseInt(idCategory.value, 10))[0]
		//* Accèder à ses produits.
		.products
		//* Pour chacun de ses prosuits, ajouter une option au SELECT des produits.
		.forEach(product => idProduct.options[idProduct.options.length] = new Option(product.name, product.idProduct));

	//* Finalement, rendre visible le DIV du SELECT des produits.
	selectProducts.style.visibility = 'visible';

});
