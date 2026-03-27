<?php

/**
 * CONFIGURAÇÕES GLOBAIS (Cérebro da Aplicação)
 * 
 * Este arquivo centraliza todas as constantes do sistema, como nomes, 
 * URLs, fuso horário e chaves de APIs externas. Além de carregar o arquivo .env
 */

// --- Carregador de Arquivo de Configuração (.env) ---
function loadEnv($path) {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}
loadEnv(dirname(__DIR__) . '/.env');

/**
 * Helper para obter variáveis de ambiente com valor padrão.
 */
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// --- Nomenclatura do Projeto ---
define('APP_NAME', 'SISTEMA_BIBLIOTECA');      // Nome oficial completo
define('APP_NAME_SHORT', 'BIBLIOTECA');       // Nome curto para a Logo

// --- Detecção Automática de URL (BASE_URL) ---
// Isso permite que o sistema funcione em qualquer PC/Pasta sem alterar código.
function getAutoBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Pega o diretório do script atual (public/index.php ou public/api.php)
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    
    // Remove o "/public" da URL para apontar para a raiz do projeto se estiver acessando pela pasta
    $base_dir = ($script_dir === '/') ? '' : str_replace('/public', '', $script_dir);
    
    return $protocol . "://" . $host . $base_dir;
}

define('BASE_URL', env('APP_URL') ?: getAutoBaseUrl());

// --- Mapeamento de Caminhos (Paths) ---
define('ROOT_PATH', dirname(__DIR__));        // Pasta pai (raiz do projeto)
define('PUBLIC_PATH', ROOT_PATH . '/public'); // Pasta de acesso público ao navegador
define('SRC_PATH', ROOT_PATH . '/src');       // Pasta que contém o código fonte PHP
define('STORAGE_PATH', ROOT_PATH . '/storage'); // Pasta para logs e uploads (se necessário)

// --- Variáveis de Branding ---
define('COLOR_PRIMARY', '#BC0000'); // Cor Institucional: Vermelho Carmim
define('COLOR_SECONDARY', '#FFFFFF'); // Cor Institucional: Branco Neve

// --- Regionalização (I18N) ---
date_default_timezone_set('America/Sao_Paulo');

// --- Integração com I.A. (LívIA Support) ---
define('GEMINI_API_KEY', env('GEMINI_API_KEY'));
define('GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-2.5-flash-lite'));
