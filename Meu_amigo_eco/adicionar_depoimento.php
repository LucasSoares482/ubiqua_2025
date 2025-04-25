<?php
require_once 'conexao.php';

// Verifica se o usuário está logado
verificarLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto = limpar_texto($_POST['texto']);
    $avaliacao = intval($_POST['avaliacao']);
    $usuario_id = $_SESSION['usuario_id'];
    
    // Valida os dados
    if (empty($texto)) {
        $_SESSION['mensagem'] = "Por favor, preencha o campo de texto do depoimento.";
        $_SESSION['tipo_mensagem'] = "erro";
        header("Location: home.php");
        exit;
    }
    
    if ($avaliacao < 1 || $avaliacao > 5) {
        $_SESSION['mensagem'] = "Avaliação inválida. Por favor, escolha entre 1 e 5 estrelas.";
        $_SESSION['tipo_mensagem'] = "erro";
        header("Location: home.php");
        exit;
    }
    
    // Insere o depoimento// Insere o depoimento
    $sql = "INSERT INTO depoimentos (usuario_id, texto, avaliacao, aprovado) VALUES (?, ?, ?, 0)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("isi", $usuario_id, $texto, $avaliacao);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Depoimento enviado com sucesso! Ele será exibido após aprovação.";
        $_SESSION['tipo_mensagem'] = "sucesso";
    } else {
        $_SESSION['mensagem'] = "Erro ao enviar depoimento: " . $stmt->error;
        $_SESSION['tipo_mensagem'] = "erro";
    }
    
    $stmt->close();
} else {
    $_SESSION['mensagem'] = "Método de envio inválido.";
    $_SESSION['tipo_mensagem'] = "erro";
}

// Redireciona de volta para a página inicial
header("Location: home.php");
exit;
?>