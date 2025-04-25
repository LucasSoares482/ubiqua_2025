<?php
require_once 'conexao.php';

// Verifica se o usuário está logado
verificarLogin();

// Processar filtros
$periodo = isset($_GET['periodo']) ? intval($_GET['periodo']) : 30;
$unidade = isset($_GET['unidade']) ? intval($_GET['unidade']) : 0;
$curso = isset($_GET['curso']) ? intval($_GET['curso']) : 0;
$turno = isset($_GET['turno']) ? intval($_GET['turno']) : 0;

// Função para buscar os dados de reciclagem
function getDadosReciclagem($periodo, $unidade, $curso, $turno) {
    global $conexao;
    
    $where = "WHERE e.data_entrega >= DATE_SUB(CURDATE(), INTERVAL $periodo DAY) AND e.confirmado = 1";
    
    if ($unidade > 0) {
        $where .= " AND tu.unidade_id = $unidade";
    }
    
    if ($curso > 0) {
        $where .= " AND tu.curso_id = $curso";
    }
    
    if ($turno > 0) {
        $where .= " AND tu.turno_id = $turno";
    }
    
    // Total por tipo de resíduo
    $sql = "SELECT 
                tr.nome, 
                SUM(e.quantidade) as total,
                (SELECT SUM(quantidade) 
                 FROM entregas e2 
                 JOIN turmas tu2 ON e2.turma_id = tu2.id 
                 WHERE e2.tipo_residuo_id = tr.id 
                 AND e2.confirmado = 1
                 AND e2.data_entrega >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL $periodo DAY), INTERVAL $periodo DAY)
                 " . ($unidade > 0 ? "AND tu2.unidade_id = $unidade" : "") . "
                 " . ($curso > 0 ? "AND tu2.curso_id = $curso" : "") . "
                 " . ($turno > 0 ? "AND tu2.turno_id = $turno" : "") . "
                ) as periodo_anterior
            FROM 
                entregas e
                JOIN turmas tu ON e.turma_id = tu.id
                JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id
            $where
            GROUP BY 
                tr.id";
    
    $resultado = $conexao->query($sql);
    
    $dados = [];
    $total = 0;
    $total_anterior = 0;
    
    while ($row = $resultado->fetch_assoc()) {
        $dados[$row['nome']] = [
            'total' => $row['total'],
            'periodo_anterior' => $row['periodo_anterior'] ?: 0
        ];
        
        $total += $row['total'];
        $total_anterior += $row['periodo_anterior'] ?: 0;
    }
    
    // Calcular percentuais de mudança
    foreach ($dados as $tipo => $valores) {
        if ($valores['periodo_anterior'] > 0) {
            $dados[$tipo]['variacao'] = (($valores['total'] - $valores['periodo_anterior']) / $valores['periodo_anterior']) * 100;
        } else {
            $dados[$tipo]['variacao'] = 100; // Se não havia dados no período anterior
        }
    }
    
    // Calcular variação total
    $variacao_total = 0;
    if ($total_anterior > 0) {
        $variacao_total = (($total - $total_anterior) / $total_anterior) * 100;
    } else {
        $variacao_total = 100;
    }
    
    return [
        'dados' => $dados,
        'total' => $total,
        'variacao_total' => $variacao_total
    ];
}

// Função para buscar ranking de turmas
function getRankingTurmas($periodo, $unidade, $curso, $turno, $limite = 5) {
    global $conexao;
    
    $where = "WHERE e.data_entrega >= DATE_SUB(CURDATE(), INTERVAL $periodo DAY) AND e.confirmado = 1";
    
    if ($unidade > 0) {
        $where .= " AND tu.unidade_id = $unidade";
    }
    
    if ($curso > 0) {
        $where .= " AND tu.curso_id = $curso";
    }
    
    if ($turno > 0) {
        $where .= " AND tu.turno_id = $turno";
    }
    
    $sql = "SELECT 
                tu.codigo as turma, 
                c.nome as curso,
                SUM(e.quantidade) as total
            FROM 
                entregas e
                JOIN turmas tu ON e.turma_id = tu.id
                JOIN cursos c ON tu.curso_id = c.id
            $where
            GROUP BY 
                tu.id
            ORDER BY 
                total DESC
            LIMIT $limite";
    
    return $conexao->query($sql);
}

// Função para buscar ranking de cursos
function getRankingCursos($periodo, $unidade, $turno, $limite = 5) {
    global $conexao;
    
    $where = "WHERE e.data_entrega >= DATE_SUB(CURDATE(), INTERVAL $periodo DAY) AND e.confirmado = 1";
    
    if ($unidade > 0) {
        $where .= " AND tu.unidade_id = $unidade";
    }
    
    if ($turno > 0) {
        $where .= " AND tu.turno_id = $turno";
    }
    
    $sql = "SELECT 
                c.nome as curso,
                SUM(e.quantidade) as total
            FROM 
                entregas e
                JOIN turmas tu ON e.turma_id = tu.id
                JOIN cursos c ON tu.curso_id = c.id
            $where
            GROUP BY 
                c.id
            ORDER BY 
                total DESC
            LIMIT $limite";
    
    return $conexao->query($sql);
}

// Função para buscar dados de evolução mensal
function getEvolucaoMensal($unidade, $curso, $turno) {
    global $conexao;
    
    $where = "WHERE e.confirmado = 1";
    
    if ($unidade > 0) {
        $where .= " AND tu.unidade_id = $unidade";
    }
    
    if ($curso > 0) {
        $where .= " AND tu.curso_id = $curso";
    }
    
    if ($turno > 0) {
        $where .= " AND tu.turno_id = $turno";
    }
    
    $sql = "SELECT 
                DATE_FORMAT(e.data_entrega, '%Y-%m') as mes,
                tr.nome as tipo,
                SUM(e.quantidade) as total
            FROM 
                entregas e
                JOIN turmas tu ON e.turma_id = tu.id
                JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id
            $where
            GROUP BY 
                mes, tr.id
            ORDER BY 
                mes ASC";
    
    $resultado = $conexao->query($sql);
    
    $dados = [];
    $meses = [];
    $tipos = [];
    
    while ($row = $resultado->fetch_assoc()) {
        if (!in_array($row['mes'], $meses)) {
            $meses[] = $row['mes'];
        }
        
        if (!in_array($row['tipo'], $tipos)) {
            $tipos[] = $row['tipo'];
        }
        
        $dados[$row['mes']][$row['tipo']] = $row['total'];
    }
    
    return [
        'meses' => $meses,
        'tipos' => $tipos,
        'dados' => $dados
    ];
}

// Função para buscar comparativo por unidade
function getComparativoPorUnidade() {
    global $conexao;
    
    $sql = "SELECT 
                u.nome as unidade,
                tr.nome as tipo,
                SUM(e.quantidade) as total
            FROM 
                entregas e
                JOIN turmas tu ON e.turma_id = tu.id
                JOIN unidades u ON tu.unidade_id = u.id
                JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id
            WHERE 
                e.confirmado = 1
            GROUP BY 
                u.id, tr.id
            ORDER BY 
                u.id, tr.id";
    
    $resultado = $conexao->query($sql);
    
    $dados = [];
    $unidades = [];
    $tipos = [];
    
    while ($row = $resultado->fetch_assoc()) {
        if (!in_array($row['unidade'], $unidades)) {
            $unidades[] = $row['unidade'];
        }
        
        if (!in_array($row['tipo'], $tipos)) {
            $tipos[] = $row['tipo'];
        }
        
        $dados[$row['unidade']][$row['tipo']] = $row['total'];
    }
    
    return [
        'unidades' => $unidades,
        'tipos' => $tipos,
        'dados' => $dados
    ];
}

// Buscar dados para a página
$dadosReciclagem = getDadosReciclagem($periodo, $unidade, $curso, $turno);
$rankingTurmas = getRankingTurmas($periodo, $unidade, $curso, $turno);
$rankingCursos = getRankingCursos($periodo, $unidade, $turno);
$evolucaoMensal = getEvolucaoMensal($unidade, $curso, $turno);
$comparativoPorUnidade = getComparativoPorUnidade();

// Buscar unidades para o filtro
$unidades_filtro = obterDados("unidades", "", "id, nome", "nome");

// Buscar cursos para o filtro
$cursos_filtro = obterDados("cursos", "", "id, nome", "nome");

// Buscar turnos para o filtro
$turnos_filtro = obterDados("turnos", "", "id, nome", "nome");

// Preparar dados para JavaScript
$meses_labels = array_map(function($mes) {
    return date('F', strtotime($mes . '-01'));
}, $evolucaoMensal['meses']);

$evolucaoJS = [];
foreach ($evolucaoMensal['tipos'] as $tipo) {
    $dataset = [
        'label' => $tipo,
        'data' => []
    ];
    
    foreach ($evolucaoMensal['meses'] as $mes) {
        $dataset['data'][] = isset($evolucaoMensal['dados'][$mes][$tipo]) ? $evolucaoMensal['dados'][$mes][$tipo] : 0;
    }
    
    $evolucaoJS[] = $dataset;
}

$comparativoJS = [
    'labels' => $comparativoPorUnidade['tipos'],
    'datasets' => []
];

foreach ($comparativoPorUnidade['unidades'] as $unidade) {
    $dataset = [
        'label' => $unidade,
        'data' => []
    ];
    
    foreach ($comparativoPorUnidade['tipos'] as $tipo) {
        $dataset['data'][] = isset($comparativoPorUnidade['dados'][$unidade][$tipo]) ? $comparativoPorUnidade['dados'][$unidade][$tipo] : 0;
    }
    
    $comparativoJS['datasets'][] = $dataset;
}

// Converter para JSON para usar no JavaScript
$evolucaoJSON = json_encode([
    'labels' => $meses_labels,
    'datasets' => $evolucaoJS
]);

$comparativoJSON = json_encode($comparativoJS);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <title>Dashboard - Ser Recicla</title>
    <link rel="stylesheet" href="style_dashboard.css">
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
        <div class="dashboard-header">
            <h1>Dashboard Ser Recicla</h1>
            <p>Monitoramento e análise de dados de reciclagem</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="filters">
            <div class="filter-group">
                <label for="filterPeriodo">Período</label>
                <select id="filterPeriodo" name="periodo">
                    <option value="7" <?php echo $periodo == 7 ? 'selected' : ''; ?>>Últimos 7 dias</option>
                    <option value="30" <?php echo $periodo == 30 ? 'selected' : ''; ?>>Últimos 30 dias</option>
                    <option value="90" <?php echo $periodo == 90 ? 'selected' : ''; ?>>Últimos 90 dias</option>
                    <option value="180" <?php echo $periodo == 180 ? 'selected' : ''; ?>>Últimos 6 meses</option>
                    <option value="365" <?php echo $periodo == 365 ? 'selected' : ''; ?>>Último ano</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterUnidade">Unidade</label>
                <select id="filterUnidade" name="unidade">
                    <option value="0">Todas as unidades</option>
                    <?php while ($unidade_filtro = $unidades_filtro->fetch_assoc()): ?>
                        <option value="<?php echo $unidade_filtro['id']; ?>" <?php echo $unidade == $unidade_filtro['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($unidade_filtro['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterCurso">Curso</label>
                <select id="filterCurso" name="curso">
                    <option value="0">Todos os cursos</option>
                    <?php while ($curso_filtro = $cursos_filtro->fetch_assoc()): ?>
                        <option value="<?php echo $curso_filtro['id']; ?>" <?php echo $curso == $curso_filtro['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($curso_filtro['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterTurno">Turno</label>
                <select id="filterTurno" name="turno">
                    <option value="0">Todos os turnos</option>
                    <?php while ($turno_filtro = $turnos_filtro->fetch_assoc()): ?>
                        <option value="<?php echo $turno_filtro['id']; ?>" <?php echo $turno == $turno_filtro['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turno_filtro['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn">Aplicar Filtros</button>
            </div>
        </form>
        
        <div class="cards-container">
            <div class="card">
                <h3>Total Reciclado</h3>
                <div class="value"><?php echo number_format($dadosReciclagem['total'], 1); ?> kg</div>
                <div class="change <?php echo $dadosReciclagem['variacao_total'] < 0 ? 'negative' : ''; ?>">
                    <?php echo ($dadosReciclagem['variacao_total'] >= 0 ? '+' : '') . number_format($dadosReciclagem['variacao_total'], 1); ?>% vs período anterior
                </div>
            </div>
            
            <?php foreach ($dadosReciclagem['dados'] as $tipo => $valores): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($tipo); ?></h3>
                    <div class="value"><?php echo number_format($valores['total'], 1); ?> kg</div>
                    <div class="change <?php echo $valores['variacao'] < 0 ? 'negative' : ''; ?>">
                        <?php echo ($valores['variacao'] >= 0 ? '+' : '') . number_format($valores['variacao'], 1); ?>% vs período anterior
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="charts-container">
            <div class="chart-card">
                <h3>Evolução por tipo de resíduo</h3>
                <div class="chart-area">
                    <canvas id="chartEvolucao"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3>Comparativo por Unidade</h3>
                <div class="chart-area">
                    <canvas id="chartUnidades"></canvas>
                </div>
            </div>
        </div>
        
        <div class="ranking-container">
            <div class="ranking-card">
                <h3>Top 5 Turmas</h3>
                <ul class="ranking-list">
                    <?php if ($rankingTurmas->num_rows > 0): ?>
                        <?php $i = 1; while ($turma = $rankingTurmas->fetch_assoc()): ?>
                            <li class="ranking-item">
                                <span class="ranking-position"><?php echo $i; ?>.</span>
                                <span class="ranking-name"><?php echo htmlspecialchars($turma['turma']); ?> - <?php echo htmlspecialchars($turma['curso']); ?></span>
                                <span class="ranking-value"><?php echo number_format($turma['total'], 1); ?> kg</span>
                            </li>
                        <?php $i++; endwhile; ?>
                    <?php else: ?>
                        <li class="ranking-item no-data">Nenhum dado disponível</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="ranking-card">
                <h3>Top 5 Cursos</h3>
                <ul class="ranking-list">
                    <?php if ($rankingCursos->num_rows > 0): ?>
                        <?php $i = 1; while ($curso = $rankingCursos->fetch_assoc()): ?>
                            <li class="ranking-item">
                                <span class="ranking-position"><?php echo $i; ?>.</span>
                                <span class="ranking-name"><?php echo htmlspecialchars($curso['curso']); ?></span>
                                <span class="ranking-value"><?php echo number_format($curso['total'], 1); ?> kg</span>
                            </li>
                        <?php $i++; endwhile; ?>
                    <?php else: ?>
                        <li class="ranking-item no-data">Nenhum dado disponível</li>
                    <?php endif; ?>
                </ul>
            </div>
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
    
    <script>
        // Configurações de cores para os gráficos
        const coresTopoResiduo = [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)'
        ];
        
        const bordaCoresTopoResiduo = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)'
        ];
        
        const coresUnidades = [
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)'
        ];
        
        // Receber dados do PHP
        const dadosEvolucao = <?php echo $evolucaoJSON; ?>;
        const dadosUnidades = <?php echo $comparativoJSON; ?>;
        
        // Adicionar cores aos datasets
        dadosEvolucao.datasets.forEach((dataset, index) => {
            dataset.backgroundColor = coresTopoResiduo[index % coresTopoResiduo.length];
            dataset.borderColor = bordaCoresTopoResiduo[index % bordaCoresTopoResiduo.length];
            dataset.borderWidth = 2;
            dataset.tension = 0.4;
        });
        
        dadosUnidades.datasets.forEach((dataset, index) => {
            dataset.backgroundColor = coresUnidades[index % coresUnidades.length];
        });
        
        // Inicializar os gráficos quando a página carregar
        window.addEventListener('DOMContentLoaded', () => {
            // Gráfico de evolução
            const ctxEvolucao = document.getElementById('chartEvolucao').getContext('2d');
            const chartEvolucao = new Chart(ctxEvolucao, {
                type: 'line',
                data: dadosEvolucao,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
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
            
            // Gráfico de comparativo por unidade
            const ctxUnidades = document.getElementById('chartUnidades').getContext('2d');
            const chartUnidades = new Chart(ctxUnidades, {
                type: 'bar',
                data: dadosUnidades,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
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