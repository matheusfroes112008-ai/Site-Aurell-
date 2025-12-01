<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_SESSION['admin_id'];
$mensagem = "";

// Buscar dados do admin
$stmt = $conn->prepare("SELECT usuario FROM admin WHERE id_admin = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($usuario);
$stmt->fetch();
$stmt->close();

// Buscar estatísticas reais (usando apenas colunas que existem)
$produtos_total = $conn->query("SELECT COUNT(*) FROM produtos")->fetch_row()[0];
$categorias = $conn->query("SELECT COUNT(*) FROM categorias")->fetch_row()[0];
$produtos_destaque = $conn->query("SELECT COUNT(*) FROM produtos WHERE destaque = 1")->fetch_row()[0];

// Calcular estoque disponível (usando estoque que existe)
$total_estoque = $conn->query("SELECT SUM(estoque) FROM produtos")->fetch_row()[0];
$total_produtos = $conn->query("SELECT COUNT(*) FROM produtos")->fetch_row()[0];
$estoque_disponivel = $total_produtos > 0 ? round(($total_estoque / ($total_produtos * 10)) * 100) : 0;

$foto_perfil = "https://i.imgur.com/4Z7kW0l.png";
$foto_padrao_local = "uploads/perfil/admin_" . $id . ".jpg";

// Processar upload de foto DEVE VIR ANTES de verificar a foto existente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $diretorio_upload = "uploads/perfil/";
    if (!is_dir($diretorio_upload)) mkdir($diretorio_upload, 0777, true);
    
    $nome_arquivo = "admin_" . $id . ".jpg";
    $caminho_completo = $diretorio_upload . $nome_arquivo;
    
    $check = getimagesize($_FILES['foto_perfil']['tmp_name']);
    if ($check !== false) {
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_completo)) {
            $foto_perfil = $caminho_completo;
            $mensagem = "<div class='success'>Foto atualizada com sucesso!</div>";
            // Recarrega a página para mostrar a nova foto
            header("Location: admin_perfil.php");
            exit();
        } else {
            $mensagem = "<div class='error'>Erro ao fazer upload da imagem.</div>";
        }
    } else {
        $mensagem = "<div class='error'>Arquivo não é uma imagem válida.</div>";
    }
}

// Verificar se existe foto personalizada (DEPOIS do processamento do upload)
if (file_exists($foto_padrao_local)) {
    $foto_perfil = $foto_padrao_local;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Meu Perfil | AURELLÉ</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

:root {
  --gold: #e4c86a;
  --gold-dark: #caa63b;
  --gold-light: #f8f3e6;
  --bg-light: #fafafa;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Montserrat', sans-serif;
  background: var(--bg-light);
  color: #333;
  min-height: 100vh;
}

.sidebar {
  width: 250px;
  background: #fff;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  border-right: 1px solid #e0e0e0;
  padding: 2rem 1rem;
  box-shadow: 2px 0 10px rgba(0,0,0,0.05);
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
  margin-bottom: 0.5rem;
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
  width: 20px;
  text-align: center;
}

.main {
  margin-left: 250px;
  padding: 2rem;
  min-height: 100vh;
}

.profile-container {
  background: #fff;
  border-radius: 16px;
  padding: 3rem;
  max-width: 1000px;
  margin: 0 auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  border: 1px solid #e0e0e0;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 3rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #f0f0f0;
}

.profile-pic-container {
  position: relative;
  flex-shrink: 0;
}

.profile-pic {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--gold);
  cursor: pointer;
  transition: all 0.3s ease;
}

.profile-pic:hover {
  opacity: 0.8;
  transform: scale(1.05);
}

.profile-pic-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s ease;
  cursor: pointer;
}

.profile-pic-container:hover .profile-pic-overlay {
  opacity: 1;
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--gold-dark);
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.profile-handle {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.profile-bio {
  color: #555;
  line-height: 1.5;
  max-width: 500px;
}

.profile-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: var(--gold-light);
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  border-left: 4px solid var(--gold);
}

.stat-number {
  font-size: 2rem;
  font-weight: 600;
  color: var(--gold-dark);
  display: block;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: #666;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.profile-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.detail-card {
  background: #f8f8f8;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e8e8e8;
}

.detail-label {
  font-size: 0.8rem;
  color: var(--gold-dark);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.detail-value {
  font-size: 1.1rem;
  color: #333;
  font-weight: 500;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  padding: 1.2rem;
  background: var(--gold);
  border: none;
  border-radius: 10px;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
  font-weight: 500;
  cursor: pointer;
  font-size: 1rem;
}

.action-btn:hover {
  background: var(--gold-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(228, 200, 106, 0.3);
}

.action-btn.secondary {
  background: transparent;
  border: 2px solid var(--gold);
  color: var(--gold-dark);
}

.action-btn.secondary:hover {
  background: var(--gold-light);
}

.action-btn.logout {
  background: transparent;
  border: 2px solid #ff6b6b;
  color: #ff6b6b;
}

.action-btn.logout:hover {
  background: #ff6b6b;
  color: white;
}

.photo-form {
  display: none;
  margin-top: 2rem;
  padding: 2rem;
  background: var(--gold-light);
  border-radius: 12px;
  border: 2px dashed var(--gold);
}

.file-input {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: white;
  font-size: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.success, .error {
  padding: 1.2rem;
  border-radius: 10px;
  margin-bottom: 2rem;
  font-weight: 500;
  text-align: center;
  font-size: 1rem;
}

.success {
  background: #f0f8f0;
  border: 1px solid #c8e6c9;
  color: #2d5a2d;
}

.error {
  background: #f8f0f0;
  border: 1px solid #e6c8c8;
  color: #a52d2d;
}

.status-active {
  color: #2d5a2d;
  font-weight: 600;
}

.status-active::before {
  content: "● ";
  color: #4caf50;
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
    padding: 1rem;
  }
  
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .profile-container {
    padding: 2rem;
  }
  
  .profile-stats,
  .profile-details,
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
</head>

<body>

<aside class="sidebar">
  <h2><i class="fas fa-gem"></i> AURELLÉ</h2>
  <a href="painel.php"><i class="fas fa-home"></i> Início</a>
  <a href="produtos.php"><i class="fas fa-ring"></i> Produtos</a>
  <a href="admin_perfil.php" class="active"><i class="fas fa-user-shield"></i> Meu Perfil</a>
  <a href="logout_admin.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</aside>

<main class="main">
  <div class="profile-container">
    <?php echo $mensagem; ?>
    
    <div class="profile-header">
      <div class="profile-pic-container">
        <img src="<?php echo $foto_perfil . '?t=' . time(); ?>" class="profile-pic" id="profileImage" onclick="togglePhotoForm()">
        <div class="profile-pic-overlay" onclick="togglePhotoForm()">
          <i class="fas fa-camera fa-2x"></i>
        </div>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?php echo htmlspecialchars($usuario); ?></div>
        <div class="profile-handle">Administrador Principal</div>
        <div class="profile-bio">
          Gerencie produtos, categorias e todo o conteúdo da joalheria AURELLÉ. 
          Mantenha o sistema atualizado e seguro.
        </div>
      </div>
    </div>

    <div class="profile-stats">
      <div class="stat-card">
        <span class="stat-number"><?php echo $produtos_total; ?></span>
        <span class="stat-label">TOTAL DE PRODUTOS</span>
      </div>
      <div class="stat-card">
        <span class="stat-number"><?php echo $categorias; ?></span>
        <span class="stat-label">CATEGORIAS</span>
      </div>
      <div class="stat-card">
        <span class="stat-number"><?php echo $produtos_destaque; ?></span>
        <span class="stat-label">EM DESTAQUE</span>
      </div>
      <div class="stat-card">
        <span class="stat-number"><?php echo $estoque_disponivel; ?>%</span>
        <span class="stat-label">ESTOQUE DISPONÍVEL</span>
      </div>
    </div>

    <div class="profile-details">
      <div class="detail-card">
        <div class="detail-label">USUÁRIO</div>
        <div class="detail-value"><?php echo htmlspecialchars($usuario); ?></div>
      </div>
      <div class="detail-card">
        <div class="detail-label">ID DO ADMINISTRADOR</div>
        <div class="detail-value">#<?php echo $id; ?></div>
      </div>
      <div class="detail-card">
        <div class="detail-label">DATA DE CADASTRO</div>
        <div class="detail-value"><?php echo date('d/m/Y'); ?></div>
      </div>
      <div class="detail-card">
        <div class="detail-label">STATUS</div>
        <div class="detail-value status-active">Ativo</div>
      </div>
    </div>

    <div class="actions-grid">
      <a href="admin_alterar_senha.php" class="action-btn secondary">
        <i class="fas fa-key"></i> Alterar Senha
      </a>
      <a href="produtos.php" class="action-btn">
        <i class="fas fa-ring"></i> Gerenciar Produtos
      </a>
      <a href="logout_admin.php" class="action-btn logout">
        <i class="fas fa-sign-out-alt"></i> Sair
      </a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="photo-form" id="photoForm">
      <input type="file" name="foto_perfil" class="file-input" accept="image/*" required>
      <div class="form-actions">
        <button type="button" class="action-btn secondary" onclick="togglePhotoForm()">Cancelar</button>
        <button type="submit" class="action-btn">Salvar Foto</button>
      </div>
    </form>
  </div>
</main>

<script>
function togglePhotoForm() {
  const form = document.getElementById('photoForm');
  form.style.display = form.style.display === 'block' ? 'none' : 'block';
}

document.querySelector('input[name="foto_perfil"]').addEventListener('change', function(e) {
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('profileImage').src = e.target.result;
    }
    reader.readAsDataURL(this.files[0]);
  }
});
</script>

</body>
</html>