<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'roadmap_system');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_CHARSET', 'utf8mb4');

// Classe para conexão com o banco
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }
        return $this->conn;
    }
}

// Funções auxiliares
function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function getStatusColor($status) {
    $colors = [
        'pendente' => 'secondary',
        'em_progresso' => 'warning',
        'em_andamento' => 'info',
        'concluido' => 'success',
        'atrasado' => 'danger',
        'planejamento' => 'light',
        'pausado' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}

function getPriorityColor($priority) {
    $colors = [
        'baixa' => 'success',
        'media' => 'warning',
        'alta' => 'danger',
        'critica' => 'dark'
    ];
    return $colors[$priority] ?? 'secondary';
}

// Inicializar conexão
$database = new Database();
$db = $database->getConnection();
?>
