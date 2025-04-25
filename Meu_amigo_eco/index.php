<?php
require_once '../conexao.php';
require_once '../funcoes.php';

// Verifica se o usuário está logado e é administrador
verificarLogin();

if ($_SESSION['usuario_perfil'] != 'admin' && $_SESSION['usuario_perfil'] != 'gestor') {
    header("Location: ../home.php");
    exit;
}

// Buscar estatísticas de reciclagem
$estatisticas = [];

// Total reciclado
$sql_total = "SELECT SUM(e.quantidade) as total FROM entregas e WHERE e.confirmado = 1";
$resultado_total = $conexao->query($sql_total);
$estatisticas['total'] = $resultado_total->fetch_assoc()['total'] ?? 0;

// Total por tipo de resíduo
$sql_por_tipo = "SELECT tr.nome, SUM(e.quantidade) as total 
                FROM entregas e 
                JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id 
                WHERE e.confirmado = 1
                GROUP BY tr.id";
$resultado_por_tipo = $conexao->query($sql_por_tipo);

$estatisticas['por_tipo'] = [];
while ($row = $resultado_por_tipo->fetch_assoc()) {
    $estatisticas['por_tipo'][$row['nome']] = $row['total'];
}

// Total de usuários
$sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$resultado_usuarios = $conexao->query($sql_usuarios);
$estatisticas['usuarios'] = $resultado_usuarios->fetch_assoc()['total'] ?? 0;

// Total de depoimentos não aprovados
$sql_depoimentos = "SELECT COUNT(*) as total FROM depoimentos WHERE aprovado = 0";
$resultado_depoimentos = $conexao->query($sql_depoimentos);
$estatisticas['depoimentos_pendentes'] = $resultado_depoimentos->fetch_assoc()['total'] ?? 0;

// Buscar últimas entregas
$sql_entregas = "SELECT e.id, e.quantidade, e.data_entrega, u.nome as usuario, tr.nome as tipo_residuo, tu.codigo as turma
                FROM entregas e
                JOIN usuarios u ON e.usuario_id = u.id
                JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id
                JOIN turmas tu ON e.turma_id = tu.id
                WHERE e.confirmado = 1
                ORDER BY e.data_entrega DESC
                LIMIT 10";
$ultimas_entregas = $conexao->query($sql_entregas);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <title>Painel Administrativo - ECO TRACK Paraense</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2b2640;
            color: white;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid #3a3456;
        }
        
        .sidebar-header img {
            height: 50px;
            margin-bottom: 10px;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
            margin: 0;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #3a3456;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            color: #2b2640;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #2b2640;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #00ff88;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-container h2 {
            color: #2b2640;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f5f5f5;
            font-weight: 600;
            color: #2b2640;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .action-btn {
            background-color: #2b2640;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
        }
        
        .action-btn.edit {
            background-color: #00adb5;
        }
        
        .action-btn.delete {
            background-color: #ff6b6b;
        }
        
        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .chart-card h2 {
            color: #2b2640;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .chart-area {
            height: 300px;
            position: relative;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2b2640;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-success {
            background-color: #00ff88;
            color: #2b2640;
        }
        
        .btn-danger {
            background-color: #ff6b6b;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-info {
            background-color: #00adb5;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../home_img/Bandeira_do_Pará.svg.png" alt="Logo">
            <h2>Painel Administrativo</h2>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Usuários</span>
            </a>
            <a href="entregas.php" class="menu-item">
                <i class="fas fa-recycle"></i>
                <span>Entregas</span>
            </a>
            <a href="turmas.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Turmas</span>
            </a>
            <a href="depoimentos.php" class="menu-item">
                <i class="fas fa-comment"></i>
                <span>Depoimentos</span>
                <?php if ($estatisticas['depoimentos_pendentes'] > 0): ?>
                    <span class="badge"><?php echo $estatisticas['depoimentos_pendentes']; ?></span>
                <?php endif; ?>
            </a>
            <a href="configuracoes.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Configurações</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Dashboard Administrativo</h1>
            <div class="user-info">
                <span>Olá, <?php echo $_SESSION['usuario_nome']; ?></span>
                <a href="../logout.php" class="btn btn-danger">Sair</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['mensagem_admin'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensagem_admin']; ?>">
                <?php echo $_SESSION['mensagem_admin']; ?>
            </div>
            <?php 
            unset($_SESSION['mensagem_admin']);
            unset($_SESSION['tipo_mensagem_admin']);
            ?>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Reciclado</h3>
                <div class="value"><?php echo formatarPeso($estatisticas['total']); ?></div>
            </div>
            
            <?php foreach ($estatisticas['por_tipo'] as $tipo => $valor): ?>
                <div class="stat-card">
                    <h3><?php echo htmlspecialchars($tipo); ?></h3>
                    <div class="value"><?php echo formatarPeso($valor); ?></div>
                </div>
            <?php endforeach; ?>
            
            <div class="stat-card">
                <h3>Total de Usuários</h3>
                <div class="value"><?php echo $estatisticas['usuarios']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Depoimentos Pendentes</h3>
                <div class="value"><?php echo $estatisticas['depoimentos_pendentes']; ?></div>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart-card">
                <h2>Reciclagem por Tipo</h2>
                <div class="chart-area">
                    <canvas id="chartTipos"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h2>Reciclagem por Mês</h2>
                <div class="chart-area">
                    <canvas id="chartMensal"></canvas>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h2>Últimas Entregas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Turma</th>
                        <th>Tipo de Resíduo</th>
                        <th>Quantidade</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ultimas_entregas->num_rows > 0): ?>
                        <?php while ($entrega = $ultimas_entregas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $entrega['id']; ?></td>
                                <td><?php echo htmlspecialchars($entrega['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($entrega['turma']); ?></td>
                                <td><?php echo htmlspecialchars($entrega['tipo_residuo']); ?></td>
                                <td><?php echo formatarPeso($entrega['quantidade']); ?></td>
                                <td><?php echo formatarData($entrega['data_entrega']); ?></td>
                                <td>
                                    <a href="editar_entrega.php?id=<?php echo $entrega['id']; ?>" class="action-btn edit">Editar</a>
                                    <a href="excluir_entrega.php?id=<?php echo $entrega['id']; ?>" class="action-btn delete" onclick="return confirm('Tem certeza que deseja excluir esta entrega?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Nenhuma entrega registrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Dados para os gráficos
        const dadosTipos = {
            labels: <?php echo json_encode(array_keys($estatisticas['por_tipo'])); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($estatisticas['por_tipo'])); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)'
                ],
                borderWidth: 1
            }]
        };
        
        // Buscar dados de reciclagem mensal (simulado para exemplo)
        const dadosMensal = {
            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'],
            datasets: [{
                label: 'Total Reciclado (kg)',
                data: [45, 60, 75, 90, 120, <?php echo $estatisticas['total']; ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        };
        
        // Inicializar os gráficos quando a página carregar
        window.addEventListener('DOMContentLoaded', () => {
            // Gráfico de tipos de resíduo
            const ctxTipos = document.getElementById('chartTipos').getContext('2d');
            const chartTipos = new Chart(ctxTipos, {
                type: 'pie',
                data: dadosTipos,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // Gráfico de evolução mensal
            const ctxMensal = document.getElementById('chartMensal').getContext('2d');
            const chartMensal = new Chart(ctxMensal, {
                type: 'line',
                data: dadosMensal,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantidade (kg)'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>