<?php
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = limpar_texto($_POST['email']);
    
    // Valida o e-mail
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensagem_newsletter'] = "Por favor, informe um e-mail válido.";
        $_SESSION['tipo_mensagem_newsletter'] = "erro";
        header("Location: home.php");
        exit;
    }
    
    // Verifica se o e-mail já está cadastrado
    $sql_verifica = "SELECT id FROM newsletter WHERE email = ?";
    $stmt_verifica = $conexao->prepare($sql_verifica);
    $stmt_verifica->bind_param("s", $email);
    $stmt_verifica->execute();
    $resultado_verifica = $stmt_verifica->get_result();
    
    if ($resultado_verifica->num_rows > 0) {
        $_SESSION['mensagem_newsletter'] = "Este e-mail já está cadastrado em nossa newsletter.";
        $_SESSION['tipo_mensagem_newsletter'] = "info";
    } else {
        // Insere o e-mail na newsletter
        $sql = "INSERT INTO newsletter (email) VALUES (?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem_newsletter'] = "E-mail cadastrado com sucesso na newsletter!";
            $_SESSION['tipo_mensagem_newsletter'] = "sucesso";
        } else {
            $_SESSION['mensagem_newsletter'] = "Erro ao cadastrar e-mail: " . $stmt->error;
            $_SESSION['tipo_mensagem_newsletter'] = "erro";
        }
        
        $stmt->close();
    }
    
    $stmt_verifica->close();
} else {
    $_SESSION['mensagem_newsletter'] = "Método de envio inválido.";
    $_SESSION['tipo_mensagem_newsletter'] = "erro";
}

// Redireciona de volta para a página inicial
header("Location: home.php");
exit;
?>