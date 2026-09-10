<?php
/**
 * GasyImmo — Connexion à la base de données
 * Fichier : config/connexion.php
 * Utilise PDO avec gestion d'erreur claire pour XAMPP
 */

define('DB_HOTE',    getenv('DB_HOTE') ?: 'localhost');
define('DB_NOM',     getenv('DB_NOM') ?: 'gasyimmo');
define('DB_UTIL',    getenv('DB_UTIL') ?: 'root');
define('DB_PASSE',   getenv('DB_PASSE') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Retourne une connexion PDO unique (singleton)
 * @return PDO
 */
function obtenirBDD(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn     = 'mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_UTIL, DB_PASSE, $options);
        } catch (PDOException $e) {
            die('
            <div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;border:1px solid #dc2626;border-radius:12px;background:#fef2f2;">
                <h2 style="color:#dc2626;margin-bottom:12px;">❌ Erreur de connexion</h2>
                <p>Impossible de se connecter à la base de données <strong>' . DB_NOM . '</strong>.</p>
                <p style="margin-top:8px;color:#666;">Détail : ' . htmlspecialchars($e->getMessage()) . '</p>
                <hr style="margin:16px 0;border-color:#fecaca;">
                <p style="font-size:13px;color:#888;">
                    ✅ Vérifiez que <strong>XAMPP</strong> est démarré (Apache + MySQL)<br>
                    ✅ Importez le fichier <strong>gasyimmo.sql</strong> dans phpMyAdmin
                </p>
            </div>');
        }
    }

    return $pdo;
}
