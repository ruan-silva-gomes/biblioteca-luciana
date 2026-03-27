<?php

/**
 * CONFIGURAÇÕES GLOBAIS (Cérebro da Aplicação)
 * 
 * Este arquivo centraliza todas as constantes do sistema, como nomes, 
 * URLs, fuso horário e chaves de APIs externas.
 */

// --- Nomenclatura do Projeto ---
define('APP_NAME', 'SISTEMA_BIBLIOTECA');      // Nome oficial completo
define('APP_NAME_SHORT', 'BIBLIOTECA');       // Nome curto para a Logo
define('BASE_URL', 'http://localhost/sistema-biblioteca'); // Endereço base no servidor

// --- Mapeamento de Caminhos (Paths) ---
// Usamos caminhos absolutos para evitar erros de inclusão (require/include)
define('ROOT_PATH', dirname(__DIR__));        // Pasta pai (raiz do projeto)
define('PUBLIC_PATH', ROOT_PATH . '/public'); // Pasta de acesso público ao navegador
define('SRC_PATH', ROOT_PATH . '/src');       // Pasta que contém o código fonte PHP
define('STORAGE_PATH', ROOT_PATH . '/storage'); // Pasta para logs e uploads (se necessário)

// --- Variáveis de Branding ---
define('COLOR_PRIMARY', '#BC0000'); // Cor Institucional: Vermelho Carmim
define('COLOR_SECONDARY', '#FFFFFF'); // Cor Institucional: Branco Neve

// --- Regionalização (I18N) ---
// Garante que todas as chamadas de date() usem o horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// --- Integração com I.A. (LívIA Support) ---
// Utilizamos o gateway OpenRouter para acessar modelos GPT/Gemini
define('OPENROUTER_API_KEY', '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');
define('OPENROUTER_MODEL', 'google/gemini-2.0-flash-lite-001');
