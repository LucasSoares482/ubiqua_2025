<?php
// Arquivo de conexão com o banco de dados
$servidor = "localhost";  // Endereço do servidor MySQL
$usuario = "root";        // Nome de usuário do MySQL (padrão é "root")
$senha = "";              // Senha do MySQL (em ambiente de desenvolvimento pode estar vazia)
$banco = "eco_track_paraense"; // Nome do banco de dados que você criou

// Estabelece a conexão
$conexao = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Configura o charset para UTF-8
$conexao->set_charset("utf8");

// Função para limpar entradas (evita injeção SQL)
function limpar_texto($texto) {
    global $conexao;
    $texto = trim($texto);
    return $conexao->real_escape_string($texto);
}

// Função para obter dados de uma tabela
function obterDados($tabela, $where = "", $campos = "*", $ordem = "") {
    global $conexao;
    
    $sql = "SELECT $campos FROM $tabela";
    
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    
    if (!empty($ordem)) {
        $sql .= " ORDER BY $ordem";
    }
    
    return $conexao->query($sql);
}

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
function estaLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Redireciona para login se não estiver logado
function verificarLogin() {
    if (!estaLogado()) {
        header("Location: login.php");
        exit;
    }
}
?>