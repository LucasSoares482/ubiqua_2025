<?php
// Inicialização do sistema - incluir no topo de cada arquivo
require_once 'config.php';
require_once 'conexao.php';
require_once 'funcoes.php';

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se existe uma mensagem flash para exibir
function exibirMensagens() {
    if (isset($_SESSION['mensagem'])) {
        $tipo = isset($_SESSION['tipo_mensagem']) ? $_SESSION['tipo_mensagem'] : 'info';
        $mensagem = $_SESSION['mensagem'];
        
        // Limpa as mensagens da sessão
        unset($_SESSION['mensagem']);
        unset($_SESSION['tipo_mensagem']);
        
        return [
            'mensagem' => $mensagem,
            'tipo' => $tipo
        ];
    }
    
    return null;
}

// Verifica se o site está em modo de manutenção
function siteEmManutencao() {
    global $conexao;
    
    $sql = "SELECT valor FROM configuracoes WHERE chave = 'manutencao'";
    $resultado = $conexao->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        return $row['valor'] === '1';
    }
    
    return false;
}

// Se o site está em manutenção e o usuário não é administrador, redireciona para a página de manutenção
if (siteEmManutencao() && (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin')) {
    // Ignora a verificação se estivermos na página de manutenção
    $url_atual = basename($_SERVER['PHP_SELF']);
    if ($url_atual !== 'manutencao.php' && $url_atual !== 'login.php') {
        header('Location: manutencao.php');
        exit;
    }
}
?>