<?php

declare(strict_types=1);

namespace peps\core;


/**
 * Classe 100% statique de routage. 
 * Offre 5 méthodes de routage:
 * route(): routage initial et, si besoin, redirection côté serveur.
 * render(): rendre une vue.
 * json(): envoyer du JSON.
 * download(): envoyer un fichier en flux binaire.
 * redirect(): redirection côté client.
 */
final class Router
{
    /**
     * Constructeur privé 
     */
    private function __construct()
    {
    }

    /***
     * Sans parametre analyse la requête du client, détermine la route et invoque la méthode appropriée du controleur approprié.
     * Avec paramètre, effectue une redirection côté serveur.
     */
    public static function route(?string $verb = null, ?string $uri = null): void
    {
        //* Si absents, récupérer le verbe HTTP et l'URI de la requête client.
        $verb = $verb ?: filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
        $uri = $uri ?: filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
        //* Si pas de verbe ou d'URI, rendre la vue 404.
        if (!$verb | !$uri) {
            //* Si aucune route trouvée, rendre la vue 404.
            self::render(Cfg::get('ERROR_404_VIEW'));
        }

        //* Charger la table de routage JSON.
        $routes = json_decode(file_get_contents(Cfg::get('ROUTE_FILE')));

        //* Parcourir la table de routage.
        foreach ($routes as $route) {

            //* Utiliser l'expression régulière de l'URI avec un slash final optionnel.
            $regexp = "@^{$route->uri}/?$@";

            //* Si une route correspondante est trouvée... 
            if (!strcasecmp($route->verb, $verb) && preg_match($regexp, $uri, $matches)) {
                //* Supprimer le premier élément du tableau.
                array_shift($matches);

                //* Si paramètres, il faut utiliser les noms fournis (si disponibles) pour obtenir un tableau associatif, sinon un tableau indicé.
                if (($assocParams = $matches) && !empty($route->params))
                    @$assocParams =  array_combine($route->params, $matches) ?: $$matches; // @ = supprime les warnings 

                //* Séparer le nom du controleur du nom de la méthode.
                [$controllerName, $methodeName] = explode('.', $route->method);

                //* Préfixer le nom du contrôleur avec son namespace.
                $controllerName = Cfg::get('CONTROLLERS_NAMESPACE') . DIRECTORY_SEPARATOR . $controllerName;

                //* Invoquer la méthode du contrôleur.
                $controllerName::$methodeName($assocParams);

                return;
            }
        }
        //* Si aucune route trouvée, rendre la vue 404.
        self::render(Cfg::get('ERROR_404_VIEW'));
    }

    /** 
     * Rend une vue.
     * 
     * @param string $view nom de la vue.
     * @param array $params Tableau associatif des paramètres à transmettre à la vue
     */
    public static function render(string $view, array $params = []): void
    {
        //* transformer chaque clé en variable.
        extract($params);

        //* Insérer la vue.
        require Cfg::get('VIEWS_DIR') . DIRECTORY_SEPARATOR . $view;
        //* Arrêter le script pour envoyer la vue vers le client.
        exit;
    }

    /**
     * Envoie au client une chaîne JSON.
     *
     * @param string $json Chaîne JSON.
     * 
     */
    public static function json(string $json): void
    {
        //* Paramètrer l'entête  HTTP du JSON.
        header('content-Type:application/json');
        //* Envoyer la chaîne JSON au client et arrêter le scipt.
        exit($json);
    }

    /**
     * Envoie au client un fichier pour download (ou intégration comme par exemple une image).
     *
     * @param string $filePath Chemin complet du fichier depuis la racine de l'application.
     * @param string $mimeType Type MIME du fichier
     * @param string $fileName Nom du fichier proposé au client.
     */
    public static function download(string $filePath, string $mimeType, string $fileName = "File"): void
    {
        //* paramétrer l'entête HTTP.
        header("Content-Type:{$mimeType}");
        header('Content-Transfer-Encoding:Binary');
        header('Content-Length:' . filesize($filePath));
        header("Content-Disposition:attachment; filename={$fileName}");

        //* Lire le fichier et l'envoyer vers le client.
        readfile($filePath);

        //* Arrêter le script.
        exit;
    }
    /**
     * Redirection côté client.
     * Envoie la requête vers le client pour demander une redirection vers une URI. 
     *
     * @param string $uri URI
     */
    public static function redirect(string $uri): void
    {
        //* Envoyer la demande de redirection vers le client.
        header("Location:{$uri}");
        //* Arrêter le script. 
        exit;
    }
}