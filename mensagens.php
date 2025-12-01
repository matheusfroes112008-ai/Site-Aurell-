<?php
session_start();
require 'config.php';

// Verifica se é admin logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Processar ações
if (isset($_GET['action'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] == 'marcar_lida') {
        $stmt = $conn->prepare("UPDATE mensagens_contato SET lida = 1 WHERE id_mensagem = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    if ($_GET['action'] == 'excluir') {
        $stmt = $conn->prepare("DELETE FROM mensagens_contato WHERE id_mensagem = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: mensagens.php");
    exit;
}

// Buscar mensagens
$result = $conn->query("SELECT * FROM mensagens_contato ORDER BY data_envio DESC");

// Estatísticas
$total = $conn->query("SELECT COUNT(*) as total FROM mensagens_contato")->fetch_assoc()['total'];
$nao_lidas = $conn->query("SELECT COUNT(*) as total FROM mensagens_contato WHERE lida = 0")->fetch_assoc()['total'];
$hoje = $conn->query("SELECT COUNT(*) as total FROM mensagens_contato WHERE DATE(data_envio) = CURDATE()")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Mensagens | Painel AURELLÉ</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

:root {
  --gold: #e4c86a;
  --gold-dark: #caa63b;
  --gold-light: #f8f3e6;
  --card-bg: #fffefb;
  --surface-border: #f2e6c6;
  --muted: #9b8b6a;
}

body {
    font-family: 'Montserrat', sans-serif;
    background: linear-gradient(to bottom right, #fffdf8, #fdfaf3);
    color: #333;
    margin: 0;
    padding: 0;
}

/* SIDEBAR */
.sidebar {
    width: 250px;
    background-color: #fff;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    border-right: 1px solid var(--surface-border);
    padding: 2rem 1rem;
}

.sidebar h2 {
    font-family: 'Playfair Display', serif;
    color: var(--gold-dark);
    font-size: 1.6rem;
    margin-bottom: 2rem;
    text-align: center;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: #555;
    text-decoration: none;
    transition: all 0.3s;
    font-weight: 500;
}

.sidebar a:hover {
    background-color: var(--gold-light);
    color: var(--gold-dark);
}

.sidebar a.active {
    background-color: var(--gold-light);
    color: var(--gold-dark);
    border-left: 3px solid var(--gold);
}

.sidebar a i {
    color: var(--gold-dark);
}

/* CONTEÚDO PRINCIPAL */
.main {
    margin-left: 260px;
    padding: 2rem;
    min-height: 100vh;
}

h1 {
    font-family: 'Playfair Display', serif;
    color: var(--gold-dark);
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* CARDS DE ESTATÍSTICAS */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--surface-border);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gold-dark);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--muted);
    font-weight: 500;
    font-size: 0.9rem;
}

/* CARDS DE MENSAGENS - ESTILO ESPECÍFICO DA IMAGEM */
.message-card {
    background: var(--card-bg);
    border: 1px solid var(--surface-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.message-card:hover {
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
}

.message-card.unread {
    border-left: 4px solid var(--gold);
    background: linear-gradient(to right, #fffefb, var(--gold-light));
}

/* CABEÇALHO NA MESMA LINHA - CORREÇÃO */
.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--surface-border);
}

.message-title {
    font-family: 'Playfair Display', serif;
    color: var(--gold-dark);
    font-size: 1.4rem;
    margin: 0;
    font-weight: 600;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.message-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-new {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #fff;
}

.status-read {
    background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.message-time {
    color: var(--muted);
    font-size: 0.9rem;
    font-weight: 500;
    background: var(--gold-light);
    padding: 0.3rem 0.8rem;
    border-radius: 6px;
}

/* INFO GRID - ESTILO DA IMAGEM */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    color: var(--gold-dark);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    color: #333;
    font-weight: 500;
    font-size: 1.1rem;
    padding: 0.8rem;
    background: var(--gold-light);
    border: 1px solid var(--surface-border);
    border-radius: 8px;
    min-height: 50px;
    display: flex;
    align-items: center;
}

/* MENSAGEM - ESTILO DA IMAGEM */
.message-section {
    margin: 2rem 0;
}

.message-label {
    color: var(--gold-dark);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
}

.message-text {
    background: var(--gold-light);
    border: 1px solid var(--surface-border);
    border-radius: 12px;
    padding: 1.5rem;
    color: #333;
    line-height: 1.6;
    white-space: pre-wrap;
    font-size: 1rem;
    min-height: 120px;
    font-weight: 500;
}

/* BOTÕES - ESTILO DA IMAGEM */
.message-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--surface-border);
}

.btn-primary {
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(202, 166, 59, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #d32f2f, #b71c1c);
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

/* ESTADO VAZIO */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--surface-border);
}

.empty-state h3 {
    font-family: 'Playfair Display', serif;
    color: var(--gold-dark);
    margin-bottom: 0.5rem;
}

/* RESPONSIVO */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 1rem;
    }
    
    .main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .message-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .message-meta {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .message-actions {
        flex-direction: column;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <h2><i class="fas fa-gem"></i> AURELLÉ</h2>
    <a href="painel.php"><i class="fas fa-home"></i> Início</a>
    <a href="produtos.php"><i class="fas fa-ring"></i> Produtos</a>
    <a href="mensagens.php" class="active"><i class="fas fa-envelope"></i> Mensagens</a>
    <a href="admin_perfil.php"><i class="fas fa-user-shield"></i> Meu Perfil</a>
    <a href="logout_admin.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</aside>

<!-- Main -->
<main class="main">
    <h1><i class="fas fa-envelope"></i> Mensagens de Contato</h1>

    <!-- Estatísticas -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total de Mensagens</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nao_lidas; ?></div>
            <div class="stat-label">Não Lidas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $hoje; ?></div>
            <div class="stat-label">Recebidas Hoje</div>
        </div>
    </div>

    <!-- Lista de Mensagens -->
    <div class="messages-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="message-card <?php echo !$row['lida'] ? 'unread' : ''; ?>">
                    <!-- CABEÇALHO NA MESMA LINHA - CORRIGIDO -->
                    <div class="message-header">
                        <h3 class="message-title"><?php echo htmlspecialchars($row['assunto']); ?></h3>
                        <div class="message-meta">
                            <span class="message-status <?php echo !$row['lida'] ? 'status-new' : 'status-read'; ?>">
                                <?php echo $row['lida'] ? 'LIDA' : 'NOVA'; ?>
                            </span>
                            <span class="message-time">
                                <?php echo date('d/m/Y H:i', strtotime($row['data_envio'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- INFORMAÇÕES DO REMETENTE -->
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">DE:</span>
                            <div class="info-value"><?php echo htmlspecialchars($row['nome']); ?></div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">EMAIL:</span>
                            <div class="info-value"><?php echo htmlspecialchars($row['email']); ?></div>
                        </div>
                        <?php if ($row['telefone']): ?>
                        <div class="info-item">
                            <span class="info-label">TELEFONE:</span>
                            <div class="info-value"><?php echo htmlspecialchars($row['telefone']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- MENSAGEM -->
                    <div class="message-section">
                        <span class="message-label">MENSAGEM:</span>
                        <div class="message-text"><?php echo htmlspecialchars($row['mensagem']); ?></div>
                    </div>

                    <!-- AÇÕES -->
                    <div class="message-actions">
                        <?php if (!$row['lida']): ?>
                            <a href="mensagens.php?action=marcar_lida&id=<?php echo $row['id_mensagem']; ?>" class="btn-primary">
                                <i class="fas fa-check"></i> Marcar como Lida
                            </a>
                        <?php endif; ?>
                        <a href="mensagens.php?action=excluir&id=<?php echo $row['id_mensagem']; ?>" class="btn-secondary" onclick="return confirm('Tem certeza que deseja excluir esta mensagem?')">
                            <i class="fas fa-trash"></i> Excluir
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Nenhuma mensagem encontrada</h3>
                <p>As mensagens recebidas aparecerão aqui.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

<?php $conn->close(); ?>