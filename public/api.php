<?php

/**
 * Roteador Central da API (JSON).
 * Este arquivo recebe todas as requisições AJAX do frontend e encaminha para o controller adequado.
 */

// Importa as configurações globais e o autoloader
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/autoload.php';

// Camada de Infraestrutura
use App\Config\Database;
use App\Infrastructure\Persistence\MySQLStudentRepository;
use App\Infrastructure\Persistence\MySQLTurmaRepository;

// Camada de Aplicação (Serviços)
use App\Application\Services\StudentService;
use App\Application\Services\TurmaService;
use App\Application\Services\ExportService;
use App\Application\Services\ChatService;

// Camada de Apresentação (Controllers)
use App\Presentation\Controllers\StudentController;
use App\Presentation\Controllers\TurmaController;
use App\Presentation\Controllers\ExportController;
use App\Presentation\Controllers\ChatController;

// Define o header de resposta como JSON para todas as requisições
header('Content-Type: application/json');

try {
    // 1. Inicializa a conexão (Infra)
    $db = (new Database())->getConnection();

    // 2. Instancia Repositórios (Infra)
    $studentRepo = new MySQLStudentRepository($db);
    $turmaRepo = new MySQLTurmaRepository($db);

    // 3. Instancia Serviços (Application)
    $studentService = new StudentService($studentRepo);
    $turmaService = new TurmaService($turmaRepo);
    $exportService = new ExportService($db);
    // A linha $chatService = new ChatService($studentRepo); será substituída abaixo.

    // 4. Instancia Controllers (Presentation) - Injetando dependências
    $studentController = new StudentController($studentService);
    $classController = new TurmaController($turmaService); // Mantido como $classController para consistência com o original
    $exportController = new ExportController($exportService);

    // IA: Injetamos Student e Turma para contexto total
    $chatService = new ChatService($studentRepo, $turmaRepo);
    $chatController = new ChatController($chatService);

    // Obtém a ação solicitada via URL (ex: api.php?action=list_students)
    $action = $_GET['action'] ?? '';

    // Roteamento manual baseado na ação
    switch ($action) {

        case 'list_students':
            echo json_encode($studentController->listAll());
            break;

        case 'list_history':
            echo json_encode($studentController->listHistory());
            break;

        case 'register_student':

            // Obtém dados JSON do corpo da requisição POST
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($studentController->register($data));
            break;

        case 'delete_student':
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode($studentController->delete($id));
            break;

        // --- Gestão de Turmas ---
        case 'list_classes':
            echo json_encode($classController->listAll());
            break;

        case 'create_class':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($classController->create($data));
            break;

        case 'delete_class':
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode($classController->delete($id));
            break;

        case 'update_student_class':
            $data = json_decode(file_get_contents('php://input'), true);
            $studentId = (int)($data['id'] ?? 0);
            $className = (string)($data['className'] ?? '');
            echo json_encode($studentController->updateClass($studentId, $className));
            break;

        // --- Controle de Acesso e Dashboard ---
        case 'record_access':
            $id = (int)($_GET['id'] ?? 0);
            echo json_encode($studentController->recordAccess($id));
            break;

        case 'dashboard_stats':
            $period = $_GET['period'] ?? 'today';
            echo json_encode($studentController->getDashboardStats($period));
            break;

        // --- Relatórios ---
        case 'export_excel':
            $period = $_GET['period'] ?? 'all';
            $date = $_GET['date'] ?? null;
            $exportController->exportExcel($period, $date);
            break;

        case 'export_students':
            $exportController->exportStudents();
            break;

        // --- Inteligência Artificial (Chat) ---
        case 'chat_ai':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($chatController->chat($data['message'] ?? ''));
            break;

        // --- Fallback para ações desconhecidas ---
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida ou não informada']);
            break;
    }
} catch (Throwable $e) {
    // Captura qualquer erro (Exception ou Error) e retorna formatado como JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => 'Erro interno no servidor: ' . $e->getMessage()
    ]);
}
