<?php
require_once 'conexao.php';

$mensagem = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = limpar_texto($_POST['usuario']);
    $email = limpar_texto($_POST['email']);
    $senha = $_POST['senha'];
    
    // Verifica se os campos estão preenchidos
    if (!empty($nome) && !empty($email) && !empty($senha)) {
        // Verifica se o e-mail já está cadastrado
        $sql_verifica = "SELECT id FROM usuarios WHERE email = ?";
        $stmt_verifica = $conexao->prepare($sql_verifica);
        $stmt_verifica->bind_param("s", $email);
        $stmt_verifica->execute();
        $resultado_verifica = $stmt_verifica->get_result();
        
        if ($resultado_verifica->num_rows > 0) {
            $mensagem = "Este e-mail já está cadastrado!";
        } else {
            // Hash da senha para segurança
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir o usuário no banco de dados
            $sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'estudante')";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            
            if ($stmt->execute()) {
                $mensagem = "Cadastro realizado com sucesso! Faça login para continuar.";
                // Redirecionamento opcional para a página de login
                header("Refresh: 2; URL=login.php");
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $stmt_verifica->close();
    } else {
        $mensagem = "Por favor, preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="login.png">
    <title>Cadastro - ECO TRACK Paraense</title>
</head>
<body>
    <header id="header">
        <div class="container">
            <div class="flex">
                <a href="home.php" class="inicio"><img class="i" src="home_img/Bandeira_do_Pará.svg.png" alt="Bandeira do Pará"></a>
                <nav>
                    <ul>
                        <li><a href="home.php">HOME</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <div class="main-login">
        <div class="right-login">
            <div class="card-login">
                <h1>Cadastro</h1>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="mensagem-alerta"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="textfield">
                        <label for="usuario">Nome de Usuário</label>
                        <input type="text" name="usuario" placeholder="Seu nome de usuário">
                    </div>
                    <div class="textfield">
                        <label for="email">Email</label>
                        <input type="email" name="email" placeholder="Seu e-mail">
                    </div>
                    <div class="textfield">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" placeholder="Sua senha">
                    </div>
                    <div class="botoes">
                        <button type="submit" class="btn-login">Cadastrar</button>
                        <a class="bot" href="login.php"><button type="button" class="btn-login">Login</button></a>
                    </div>
                </form>
            </div>
        </div>
        <div class="left-login">
            <h1>Cadastre-se <br> ou faça login</h1>
            <img src="home_img/fingerprint-animate.svg" class="login-png" alt="Ilustração de cadastro">
        </div>
    </div>
</body>
</html>