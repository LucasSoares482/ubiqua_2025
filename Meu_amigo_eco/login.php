<?php
require_once 'conexao.php';

$mensagem = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = limpar_texto($_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Verifica se os campos estão preenchidos
    if (!empty($usuario) && !empty($senha)) {
        // Busca o usuário no banco de dados
        $sql = "SELECT id, nome, senha, perfil FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario_db = $resultado->fetch_assoc();
            
            // Verifica se a senha está correta (usando password_verify)
            if (password_verify($senha, $usuario_db['senha'])) {
                // Inicia a sessão
                session_start();
                $_SESSION['usuario_id'] = $usuario_db['id'];
                $_SESSION['usuario_nome'] = $usuario_db['nome'];
                $_SESSION['usuario_perfil'] = $usuario_db['perfil'];
                
                // Redireciona para a página principal
                header("Location: home.php");
                exit;
            } else {
                $mensagem = "Senha incorreta!";
            }
        } else {
            $mensagem = "Usuário não encontrado!";
        }
        
        $stmt->close();
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.png">
    <title>Login - ECO TRACK Paraense</title>
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
        <div class="left-login">
            <h1>Faça login <br> ou cadastre-se</h1>
            <img src="home_img/login-animate.svg" class="login-png" alt="Ilustração de login">
        </div>
        <div class="right-login">
            <div class="card-login">
                <h1>Login</h1>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="mensagem-alerta"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="textfield">
                        <label for="usuario">Usuário (E-mail)</label>
                        <input type="text" name="usuario" placeholder="Seu e-mail">
                    </div>
                    <div class="textfield">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" placeholder="Sua senha">
                    </div>
                    <div class="botoes">
                        <button type="submit" class="btn-login">Login</button>
                        <a class="bot" href="cadastro.php"><button type="button" class="btn-login">Cadastro</button></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>