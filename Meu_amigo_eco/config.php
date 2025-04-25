<?php
// Arquivo de configurações globais

// Configurações do site
define('SITE_NAME', 'ECO TRACK Paraense');
define('SITE_DESCRIPTION', 'Reciclagem para COP30');
define('SITE_URL', 'http://localhost/eco-track-paraense'); // Ajuste conforme o seu ambiente

// Configurações de e-mail
define('EMAIL_FROM', 'contato@ecotrackaparaense.com.br');
define('EMAIL_NAME', 'ECO TRACK Paraense');

// Configurações de banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eco_track_paraense');

// Versão do sistema
define('SYSTEM_VERSION', '1.0.0');

// Configurações específicas para o projeto
define('ALLOWED_UPLOAD_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Altere para 1 em produção com HTTPS

// Definir fuso horário
date_default_timezone_set('America/Belem');
?>