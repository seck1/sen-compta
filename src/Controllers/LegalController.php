<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Pages legales (RGPD + loi senegalaise 2008-12 / CDP).
 * Accessibles publiquement, sans authentification.
 */
class LegalController {

    private function render(string $view, string $titre): void {
        $pageLegalTitre = $titre;
        ob_start();
        require APP_ROOT . "/views/legal/$view.php";
        $contenuLegal = ob_get_clean();
        require APP_ROOT . '/views/legal/_layout.php';
    }

    public function confidentialite(): void {
        $this->render('confidentialite', 'Politique de confidentialité');
    }

    public function mentions(): void {
        $this->render('mentions', 'Mentions légales');
    }

    public function cgu(): void {
        $this->render('cgu', "Conditions générales d'utilisation");
    }

    public function cookies(): void {
        $this->render('cookies', 'Politique de cookies');
    }
}
