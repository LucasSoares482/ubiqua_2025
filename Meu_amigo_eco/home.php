<?php
require_once 'conexao.php';

// Buscar depoimentos aprovados
$sql_depoimentos = "SELECT d.*, u.nome, u.email FROM depoimentos d 
                   JOIN usuarios u ON d.usuario_id = u.id 
                   WHERE d.aprovado = 1 
                   ORDER BY d.created_at DESC LIMIT 5";
$depoimentos = $conexao->query($sql_depoimentos);

// Buscar estatísticas de reciclagem
$sql_estatisticas = "SELECT tr.nome, SUM(e.quantidade) as total 
                    FROM entregas e 
                    JOIN tipos_residuos tr ON e.tipo_residuo_id = tr.id 
                    GROUP BY tr.id";
$estatisticas = $conexao->query($sql_estatisticas);

$total_reciclado = 0;
$estatisticas_por_tipo = [];

while ($estatistica = $estatisticas->fetch_assoc()) {
    $estatisticas_por_tipo[$estatistica['nome']] = $estatistica['total'];
    $total_reciclado += $estatistica['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style3.css">
    <link rel="shortcut icon" type="imagex/png" href="icone/Icone 2.png">
    <title>ECO TRACK Paraense - Reciclagem para COP30</title>
</head>
<body>
    <header id="header">
        <div class="container">
            <div class="flex">
                <a href="home.php" class="inicio"><img class="ii" src="home_img/Bandeira_do_Pará.svg.png" alt="Bandeira do Pará"></a>
                <nav>
                    <ul>
                        <li><a href="home.php">HOME</a></li>
                        <li><a href="entrega.php">RECICLAGEM</a></li>
                        <li><a href="dashboard.php">DASHBOARD</a></li>
                    </ul>
                </nav>
                <?php if (estaLogado()): ?>
                    <div class="user-info">
                        <span>Olá, <?php echo $_SESSION['usuario_nome']; ?></span>
                        <a href="logout.php"><button>Sair</button></a>
                    </div>
                <?php else: ?>
                    <div class="cadastre-se">
                        <a href="cadastro.php"><button>Registrar</button></a>
                    </div>
                    <div class="login">
                        <a href="login.php"><button>Logar</button></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="banner">
        <div class="banner-content">
            <h1><span>ECO TRACK</span> PARAENSE</h1>
            <p class="banner-subtitle">Juntos pela sustentabilidade na COP30</p>
            <a href="entrega.php" class="btn-banner">Participar Agora</a>
        </div>
    </section>

    <!-- Seção de Reciclagem -->
    <section class="reciclagem-section">
        <div class="container">
            <div class="reciclagem-header">
                <div class="reciclagem-icon">
                    <i class="fas fa-recycle"></i>
                </div>
                <h2>RECICLAGEM PARA COP30</h2>
                <p class="reciclagem-subtitle">Projeto Ser Recicla - UNAMA</p>
            </div>

            <div class="reciclagem-cards">
                <div class="reciclagem-card">
                    <div class="card-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Sustentabilidade</h3>
                    <p>Em 2025, Belém será sede da COP 30, um dos eventos globais mais importantes sobre mudanças climáticas.</p>
                    <a href="sustentabilidade.php" class="card-link">Saiba mais</a>
                </div>

                <div class="reciclagem-card">
                    <div class="card-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3>Participação</h3>
                    <p>O projeto Ser Recicla visa fomentar a participação das turmas na entrega de materiais recicláveis como alumínio, vidro, pano e PET.</p>
                    <a href="participacao.php" class="card-link">Participar</a>
                </div>

                <div class="reciclagem-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Resultados</h3>
                    <p>Acompanhe o impacto da sua contribuição através do nosso dashboard de reciclagem e veja a diferença que estamos fazendo juntos.</p>
                    <a href="dashboard.php" class="card-link">Ver Dashboard</a>
                </div>
            </div>

            <div class="reciclagem-cta">
                <h3>Como funciona?</h3>
                <div class="reciclagem-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <p>Separe seus materiais recicláveis</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <p>Entregue nos pontos de coleta da UNAMA</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <p>Registre sua contribuição no sistema</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <p>Acompanhe o impacto no dashboard</p>
                    </div>
                </div>
                <div class="cta-buttons">
                    <a href="entrega.php" class="btn-reciclar">Registrar Entrega de Resíduos</a>
                    <a href="dashboard.php" class="btn-dashboard">Ver Dashboard de Reciclagem</a>
                </div>
            </div>
        </div>
    </section>

    <div class="divisao"></div>
    
    <!-- Seção de comentários -->
    <section id="testimonials">
        <div class="testimonial-heading">
            <span>Comentário</span>
            <h1>Depoimentos</h1>
        </div>
        <div class="testimonia-box-container">
            <?php if ($depoimentos->num_rows > 0): ?>
                <?php while ($depoimento = $depoimentos->fetch_assoc()): ?>
                    <div class="testimonial-box">
                        <div class="box-top">
                            <div class="profile">
                                <div class="profile-img">
                                    <img src="pessoas_comentarios/<?php echo rand(1, 5); ?>.png" alt="">
                                </div>
                                <div class="name-user">
                                    <strong><?php echo htmlspecialchars($depoimento['nome']); ?></strong>
                                    <span>@<?php echo strtolower(str_replace(' ', '', $depoimento['nome'])); ?></span>
                                </div>
                            </div>

                            <div class="reviews">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $depoimento['avaliacao']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="client-comment">
                            <p><?php echo htmlspecialchars($depoimento['texto']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Depoimentos estáticos do HTML original caso não haja depoimentos no banco -->
                <!-- [original testimonials here] -->
            <?php endif; ?>
        </div>
        
        <?php if (estaLogado()): ?>
            <div class="add-testimonial">
                <h3>Compartilhe sua experiência</h3>
                <form action="adicionar_depoimento.php" method="post">
                    <textarea name="texto" placeholder="Conte-nos sobre sua experiência com o projeto" required></textarea>
                    <div class="rating-select">
                        <label>Avaliação:</label>
                        <select name="avaliacao" required>
                            <option value="5">5 estrelas</option>
                            <option value="4">4 estrelas</option>
                            <option value="3">3 estrelas</option>
                            <option value="2">2 estrelas</option>
                            <option value="1">1 estrela</option>
                        </select>
                    </div>
                    <button type="submit">Enviar Depoimento</button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <div class="divisao"></div>

    <!-- Footer -->
    <footer id="final">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="src/Prefeitura_belém.png" alt="Logo Prefeitura de Belém">
                </div>
                <p class="footer-slogan">Juntos pela sustentabilidade em Belém</p>
                <div class="footer-social">
                    <a href="#" class="social-icon" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-heading">Navegação</h3>
                <ul class="footer-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="entrega.php">Reciclagem</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="cadastro.php">Cadastro</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-heading">Contato</h3>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>(91) 3344-5566</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>MeuAmigoEco@gmail.com</span>
                    </li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3 class="footer-heading">Newsletter</h3>
                <p>Receba novidades sobre reciclagem e a COP30</p>
                <form class="footer-form" action="adicionar_newsletter.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Seu e-mail" required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <div class="footer-partners">
                    <h4>Parceiros:</h4>
                    <div class="partners-logos">
                        <span>UNAMA</span>
                        <span>COP30</span>
                        <span>Prefeitura</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&#169; 2025 ECO TRACK PARAENSE - A SERVIÇO DA UNAMA - COP30</p>
            <p>Desenvolvido com <i class="fas fa-heart"></i> para um futuro sustentável</p>
        </div>
    </footer>

    <script src="menu.js"></script>
</body>
</html>