<?php
// Clé API lue depuis l'environnement (.env) — NE JAMAIS mettre la vraie clé en dur ici.
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');
define('ANTHROPIC_MODEL', getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6');
