<?php
require_once 'conexao.php';

// Verifica se o usuário está logado
verificarLogin();

$mensagem = "";
$tipo_mensagem = "";

// Busca os dados para os selects
$tipos_residuos = obterDados("tipos_residuos", "", "id, nome", "nome");
$cursos = obterDados("cursos", "", "id, nome", "nome");
$turnos = obterDados("turnos", "", "id, nome", "nome");
$unidades = obterDados("unidades", "", "id, nome", "nome");

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantidade = floatval($_POST['quantidade']);
    $tipo_residuo_id = intval($_POST['tipoResiduo']);
    $turma_codigo = limpar_texto($_POST['turma']);
    $curso_id = intval($_POST['curso']);
    $semestre = intval($_POST['semestre']);
    $turno_id = intval($_POST['turno']);
    $unidade_id = intval($_POST['unidade']);
    
    // Verifica se a turma já existe ou precisa ser criada
    $sql_turma = "SELECT id FROM turmas WHERE codigo = ? AND curso_id = ? AND semestre = ? AND turno_id = ? AND unidade_id = ?";
    $stmt_turma = $conexao->prepare($sql_turma);
    $stmt_turma->bind_param("siiii", $turma_codigo, $curso_id, $semestre, $turno_id, $unidade_id);
    $stmt_turma->execute();
    $resultado_turma = $stmt_turma->get_result();
    
    if ($resultado_turma->num_rows > 0) {
        // Turma já existe
        $turma_id = $resultado_turma->fetch_assoc()['id'];
    } else {
        // Criar nova turma
        $sql_nova_turma = "INSERT INTO turmas (codigo, curso_id, semestre, turno_id, unidade_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_nova_turma = $conexao->prepare($sql_nova_turma);
        $stmt_nova_turma->bind_param("siiii", $turma_codigo, $curso_id, $semestre, $turno_id, $unidade_id);
        
        if ($stmt_nova_turma->execute()) {
            $turma_id = $stmt_nova_turma->insert_id;
        } else {
            $mensagem = "Erro ao criar a turma: " . $stmt_nova_turma->error;
            $tipo_mensagem = "erro";
        }
        
        $stmt_nova_turma->close();
    }
    
    $stmt_turma->close();
    
    // Se temos uma turma válida, registramos a entrega
    if (isset($turma_id) && $turma_id > 0) {
        // Registra a entrega usando a procedure
        $sql = "CALL sp_registrar_entrega(?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $usuario_id = $_SESSION['usuario_id'];
        $stmt->bind_param("iiidi", $usuario_id, $turma_id, $tipo_residuo_id, $quantidade, $usuario_id);
        
        if ($stmt->execute()) {
            $mensagem = "Entrega registrada com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao registrar entrega: " . $stmt->error;
            $tipo_mensagem = "erro";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Entrega de Resíduos - Ser Recicla</title>
    <link rel="stylesheet" href="style_entrega.css">
</head>
<body>
    <header>
        <div class="header-container">
            <img src="home_img/Bandeira_do_Pará.svg.png" alt="Bandeira do Pará" class="logo">
            <div class="nav-links">
                <a href="home.php">HOME</a>
                <a href="dashboard.php">DASHBOARD</a>
                <a href="entrega.php">ENTREGAR RESÍDUOS</a>
            </div>
            <a href="logout.php"><button class="logout-btn">Logout</button></a>
        </div>
    </header>
    
    <div class="main-content">
        <div class="form-container">
            <h2 class="form-title">REGISTRAR ENTREGA</h2>
            
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
            
            <form id="entregaForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="quantidade">Quantidade (kg)</label>
                    <input type="number" id="quantidade" name="quantidade" step="0.01" min="0.01" placeholder="Ex: 2.5" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipoResiduo">Tipo de Resíduo</label>
                        <select id="tipoResiduo" name="tipoResiduo" required>
                            <option value="">Selecione</option>
                            <?php while ($tipo = $tipos_residuos->fetch_assoc()<?php while ($tipo = $tipos_residuos->fetch_assoc()): ?>
                                <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="turma">Turma</label>
                        <input type="text" id="turma" name="turma" placeholder="Ex: CC3VA" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="curso">Curso</label>
                        <select id="curso" name="curso" required>
                            <option value="">Selecione</option>
                            <?php while ($curso = $cursos->fetch_assoc()): ?>
                                <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="semestre">Semestre</label>
                        <select id="semestre" name="semestre" required>
                            <option value="">Selecione</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?>º</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="turno">Turno</label>
                        <select id="turno" name="turno" required>
                            <option value="">Selecione</option>
                            <?php while ($turno = $turnos->fetch_assoc()): ?>
                                <option value="<?php echo $turno['id']; ?>"><?php echo htmlspecialchars($turno['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="unidade">Unidade</label>
                        <select id="unidade" name="unidade" required>
                            <option value="">Selecione</option>
                            <?php while ($unidade = $unidades->fetch_assoc()): ?>
                                <option value="<?php echo $unidade['id']; ?>"><?php echo htmlspecialchars($unidade['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">REGISTRAR ENTREGA</button>
            </form>
        </div>
    </div>
    
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>O Melhor para nossa cidade!</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-col">
                <h3>UNAMA</h3>
                <p>Projeto Ser Recicla</p>
            </div>
            
            <div class="footer-col">
                <h3>Contato</h3>
                <p>Entre em contato com a equipe do projeto</p>
                <div class="contact-input">
                    <input type="text" placeholder="Seu email">
                    <button><i class="fas fa-envelope"></i></button>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            &#169; 2025 Universidade da Amazônia - COP30
        </div>
    </footer>
</body>
</html>