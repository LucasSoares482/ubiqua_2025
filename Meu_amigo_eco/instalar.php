<?php
// Script para criar a estrutura do banco de dados e dados iniciais
$servidor = "localhost";
$usuario = "root";
$senha = "";

// Conectar ao MySQL sem selecionar um banco de dados
$conexao = new mysqli($servidor, $usuario, $senha);

// Verificar conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Criar o banco de dados
$sql_criar_banco = "CREATE DATABASE IF NOT EXISTS eco_track_paraense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conexao->query($sql_criar_banco) === FALSE) {
    die("Erro ao criar o banco de dados: " . $conexao->error);
}

// Selecionar o banco de dados criado
$conexao->select_db("eco_track_paraense");

// Executar o script SQL completo
$sql_script = file_get_contents('script_completo.sql');
$queries = explode(';', $sql_script);

$sucesso = true;
$erros = [];

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if ($conexao->query($query) === FALSE) {
            $sucesso = false;
            $erros[] = "Erro na query: " . $conexao->error . " (Query: " . substr($query, 0, 100) . "...)";
        }
    }
}

// Fechar a conexão
$conexao->close();

// Exibir resultado
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - ECO TRACK Paraense</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #2b2640;
            border-bottom: 2px solid #2b2640;
            padding-bottom: 10px;
        }
        
        .success {
            color: green;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        ul {
            margin: 0;
        }
        
        .btn {
            display: inline-block;
            background-color: #2b2640;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .btn:hover {
            background-color: #3a3456;
        }
    </style>
</head>
<body>
    <h1>Instalação do ECO TRACK Paraense</h1>
    
    <?php if ($sucesso): ?>
        <div class="success">
            <p><strong>Instalação concluída com sucesso!</strong></p>
            <p>O banco de dados foi criado e todas as tabelas foram configuradas corretamente.</p>
            <p>Para acessar o sistema, use as credenciais padrão:</p>
            <ul>
                <li><strong>E-mail:</strong> admin@ecotrackaparaense.com.br</li>
                <li><strong>Senha:</strong> admin123</li>
            </ul>
            <p>Lembre-se de alterar essa senha após o primeiro acesso por motivos de segurança.</p>
        </div>
    <?php else: ?>
        <div class="error">
            <p><strong>Erro na instalação!</strong></p>
            <p>Ocorreram erros durante a criação das tabelas do banco de dados.</p>
        </div>
        
        <div class="error-list">
            <h3>Detalhes dos erros:</h3>
            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?php echo $erro; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div>
        <a href="index.php" class="btn">Ir para a página inicial</a>
        <a href="login.php" class="btn">Ir para o login</a>
    </div>
</body>
</html>