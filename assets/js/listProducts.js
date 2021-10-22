"use strict";


/**
 * Supprime un produit et ses images.
 * @var idProduct PK du produit.
 *@returns void
 */
function deleteAll(idProduct) {
    //* Si l'utilisateur le confirme, rediriger vers la route adéquate.
    if (confirm("Vraiment supprimer le produit et ses photos ?")) {
        //*Solution Synchrone => voir dans productController methode delete().
        //location = `/product/delete/${idProduct}/all`;

        //* Solution Asynchrone
        let url = `/product/delete/${idProduct}/all`;
        fetch(url)
            .then(() => location.reload())
            .catch(error => console.error(error));

    }
}

/**
 * Supprimes les images d'un produit
 * @var idProduct pk du produit
 * @returns void
 */
function deleteImg(idProduct) {
    //* Si l'utilisateur le confirme, rediriger vers la route adéquate.
    if (confirm("Vraiment supprimer les photos du produit ?")) {
        //*Solution Synchrone => voir dans productController methode delete().
        // location = `/product/delete/${idProduct}/img`;

        //* Solution Asynchrone
        let url = `/product/delete/${idProduct}/img`;
        fetch(url)
            .then(() => location.reload())
            .catch(error => console.error(error));
    }

}