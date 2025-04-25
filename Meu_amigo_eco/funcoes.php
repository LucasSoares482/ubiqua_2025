<?php
require_once 'config.php';

// Função para formatar valores em kg
function formatarPeso($peso) {
    return number_format($peso, 1, ',', '.') . ' kg';
}

// Função para formatar datas
function formatarData($data, $formato = 'd/m/Y') {
    $timestamp = strtotime($data);
    return date($formato, $timestamp);
}

// Função para calcular porcentagem de variação
function calcularVariacao($valor_atual, $valor_anterior) {
    if ($valor_anterior == 0) {
        return 100; // Se o valor anterior for zero, considera 100% de aumento
    }
    
    return (($valor_atual - $valor_anterior) / $valor_anterior) * 100;
}

// Função para formatar porcentagem
function formatarPorcentagem($porcentagem) {
    $sinal = $porcentagem >= 0 ? '+' : '';
    return $sinal . number_format($porcentagem, 1, ',', '.') . '%';
}

// Função para gerar cor aleatória para gráficos
function gerarCorAleatoria($transparencia = 0.6) {
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    
    return "rgba($r, $g, $b, $transparencia)";
}

// Função para obter o nome do mês
function getNomeMes($mes) {
    $meses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];
    
    return isset($meses[$mes]) ? $meses[$mes] : '';
}

// Função para converter kg em impacto ambiental
function calcularImpactoAmbiental($kg, $tipo_residuo) {
    // Valores aproximados para cálculo de impacto ambiental
    $impactos = [
        'aluminio' => [
            'agua_economizada' => 800, // litros de água economizados por kg
            'energia_economizada' => 15, // kWh economizados por kg
            'co2_evitado' => 9 // kg de CO2 evitados por kg
        ],
        'vidro' => [
            'agua_economizada' => 330,
            'energia_economizada' => 0.3,
            'co2_evitado' => 0.3
        ],
        'pano' => [
            'agua_economizada' => 6000,
            'energia_economizada' => 3.5,
            'co2_evitado' => 3.6
        ],
        'pet' => [
            'agua_economizada' => 500,
            'energia_economizada' => 5.3,
            'co2_evitado' => 2.5
        ]
    ];
    
    // Se o tipo de resíduo não estiver definido, retorna zeros
    if (!isset($impactos[$tipo_residuo])) {
        return [
            'agua_economizada' => 0,
            'energia_economizada' => 0,
            'co2_evitado' => 0
        ];
    }
    
    return [
        'agua_economizada' => $kg * $impactos[$tipo_residuo]['agua_economizada'],
        'energia_economizada' => $kg * $impactos[$tipo_residuo]['energia_economizada'],
        'co2_evitado' => $kg * $impactos[$tipo_residuo]['co2_evitado']
    ];
}

// Função para sanitizar entrada de texto (além da função limpar_texto em conexao.php)
function sanitizar($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

// Função para verificar se um arquivo é uma imagem válida
function ehImagemValida($arquivo) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    
    return in_array($arquivo['type'], $tipos_permitidos);
}

// Função para gerar um nome de arquivo único
function gerarNomeArquivoUnico($arquivo) {
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    return uniqid() . '.' . $extensao;
}

// Função para enviar um e-mail
function enviarEmail($para, $assunto, $mensagem) {
    $headers = 'From: ' . EMAIL_NAME . ' <' . EMAIL_FROM . '>' . "\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    
    return mail($para, $assunto, $mensagem, $headers);
}
?>