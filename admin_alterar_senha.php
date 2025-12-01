<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_SESSION['admin_id'];
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Buscar senha atual do banco
    $stmt = $conn->prepare("SELECT senha FROM admin WHERE id_admin = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($senha_hash);
    $stmt->fetch();
    $stmt->close();
    
    // Verificar senha atual
    if (password_verify($senha_atual, $senha_hash)) {
        if ($nova_senha === $confirmar_senha) {
            if (strlen($nova_senha) >= 6) {
                // Atualizar senha no banco de dados
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE admin SET senha = ? WHERE id_admin = ?");
                $stmt_update->bind_param("si", $nova_senha_hash, $id);
                
                if ($stmt_update->execute()) {
                    $mensagem = "<div class='success'>Senha alterada com sucesso!</div>";
                } else {
                    $mensagem = "<div class='error'>Erro ao atualizar senha no banco de dados.</div>";
                }
                $stmt_update->close();
            } else {
                $mensagem = "<div class='error'>A nova senha deve ter pelo menos 6 caracteres.</div>";
            }
        } else {
            $mensagem = "<div class='error'>As novas senhas não coincidem.</div>";
        }
    } else {
        $mensagem = "<div class='error'>Senha atual incorreta.</div>";
    }
}

// Buscar dados do admin para mostrar na página
$stmt_user = $conn->prepare("SELECT usuario FROM admin WHERE id_admin = ?");
$stmt_user->bind_param("i", $id);
$stmt_user->execute();
$stmt_user->bind_result($usuario);
$stmt_user->fetch();
$stmt_user->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Alterar Senha | Painel AURELLÉ</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

:root {
  --gold: #e4c86a;
  --gold-dark: #caa63b;
  --card-bg: #fffefb;
  --surface-border: #f2e6c6;
  --muted: #9b8b6a;
}

body {
  font-family: 'Montserrat', sans-serif;
  background: linear-gradient(to bottom right, #fffdf8, #fdfaf3);
  color: #333;
}

.sidebar {
  width: 250px;
  background-color: #fff;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  border-right: 1px solid #f0e6c8;
  padding: 2rem 1rem;
}

.sidebar h2 {
  font-family: 'Playfair Display', serif;
  color: var(--gold-dark);
  font-size: 1.7rem;
  margin-bottom: 2rem;
  text-align: center;
}

.sidebar a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  color: #555;
  font-weight: 500;
  text-decoration: none;
  transition: .3s;
}

.sidebar a:hover {
  background-color: #fff9e9;
  color: var(--gold-dark);
}

.sidebar a.active {
  background-color: #fff9e9;
  color: var(--gold-dark);
  border-left: 3px solid var(--gold);
}

.sidebar a i { color: var(--gold-dark); }

.main {
  margin-left: 270px;
  padding: 3rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.card {
  background: var(--card-bg);
  padding: 2.5rem;
  border-radius: 16px;
  border: 1px solid var(--surface-border);
  box-shadow: 0 6px 30px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 500px;
}

.section-title {
  font-family: 'Playfair Display', serif;
  color: var(--gold-dark);
  font-size: 1.8rem;
  margin-bottom: 2rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  font-weight: 600;
  color: var(--gold-dark);
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e8e0c8;
  border-radius: 10px;
  background-color: #fff;
  font-size: 0.95rem;
  color: #222;
  font-family: 'Montserrat', sans-serif;
}

input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 6px rgba(228,200,106,0.15);
  outline: none;
}

.btn {
  padding: 0.8rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  border: none;
  font-size: 1rem;
  font-family: 'Montserrat', sans-serif;
  width: 100%;
  margin-bottom: 0.8rem;
}

.btn-gold {
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  color: #fff;
  box-shadow: 0 4px 10px rgba(228, 200, 106, 0.3);
}

.btn-gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(228, 200, 106, 0.4);
}

.btn-gray {
  background: #f5f5f5;
  color: #555;
  border: 1px solid #e0e0e0;
}

.btn-gray:hover {
  background: #eeeeee;
  transform: translateY(-2px);
}

.success, .error {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 500;
  text-align: center;
}

.success {
  background: #fdf9ea;
  border: 1px solid var(--gold);
  color: var(--gold-dark);
}

.error {
  background: #ffeaea;
  border: 1px solid #ffb6b6;
  color: #b20000;
}

.user-info {
  background: #f9f5eb;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border-left: 3px solid var(--gold);
}

.user-info strong {
  color: var(--gold-dark);
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    padding: 1rem;
  }
  
  .main {
    margin-left: 0;
    padding: 1.5rem;
  }
}
</style>
</head>

<body>

<aside class="sidebar">
  <h2><i class="fas fa-gem"></i> AURELLÉ</h2>
  <a href="painel.php"><i class="fas fa-home"></i> Início</a>
  <a href="produtos.php"><i class="fas fa-ring"></i> Produtos</a>
  <a href="admin_perfil.php"><i class="fas fa-user-shield"></i> Meu Perfil</a>
  <a href="admin_alterar_senha.php" class="active"><i class="fas fa-key"></i> Alterar Senha</a>
  <a href="logout_admin.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</aside>

<main class="main">
  <div class="card">
    <h1 class="section-title">Alterar Senha</h1>
    
    <?php echo $mensagem; ?>
    
    <div class="user-info">
      <strong>Usuário</strong>
      <?php echo htmlspecialchars($usuario); ?>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label for="senha_atual">Senha Atual</label>
        <input type="password" id="senha_atual" name="senha_atual" required>
      </div>
      
      <div class="form-group">
        <label for="nova_senha">Nova Senha</label>
        <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
      </div>
      
      <div class="form-group">
        <label for="confirmar_senha">Confirmar Nova Senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
      </div>
      
      <button type="submit" class="btn btn-gold">
        <i class="fas fa-save"></i> Alterar Senha
      </button>
      
      <a href="admin_perfil.php" class="btn btn-gray">
        <i class="fas fa-arrow-left"></i> Voltar
      </a>
    </form>
  </div>
</main>

</body>
</html>