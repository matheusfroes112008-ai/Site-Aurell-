<?php
ob_start();
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// Valida ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produtos.php?erro=invalid_id");
    exit();
}

$id = intval($_GET['id']);

// Busca produto
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id_produto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if (!$produto) {
    header("Location: produtos.php?erro=notfound");
    exit();
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");

// Remover imagem atual
if (isset($_POST['remover_imagem'])) {
    if ($produto['imagem'] && file_exists("uploads/" . $produto['imagem'])) {
        unlink("uploads/" . $produto['imagem']);
    }
    $conn->query("UPDATE produtos SET imagem = NULL WHERE id_produto = $id");
    header("Location: editar_produto.php?id=$id");
    exit();
}

// Salvar alterações
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salvar'])) {
    $nome = trim($_POST['nome']);
    $id_categoria = intval($_POST['id_categoria']);
    $preco = floatval(str_replace(',', '.', str_replace('.', '', $_POST['preco'])));
    $estoque = intval($_POST['estoque']);
    $destaque = isset($_POST['destaque']) ? 1 : 0;

    // Permitir edição de descrição se enviada no form
    $descricao_in_form = isset($_POST['descricao']) ? trim($_POST['descricao']) : null;

    $imagem = $produto['imagem'];
    if (!empty($_FILES['imagem']['name'])) {
        // gera nome seguro único
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $imagem = time() . '_' . uniqid() . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['imagem']['tmp_name'], "uploads/" . $imagem);
    }

    $stmt = $conn->prepare("UPDATE produtos SET nome=?, id_categoria=?, preco=?, estoque=?, destaque=?, imagem=? WHERE id_produto=?");
    $stmt->bind_param("sidisii", $nome, $id_categoria, $preco, $estoque, $destaque, $imagem, $id);

    if ($stmt->execute()) {
        // Atualiza também o card correspondente em colecoes.html (mantendo sua lógica)
        $novoNome = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $novoDesc = htmlspecialchars($produto['descricao'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (!empty($descricao_in_form)) {
            $novoDesc = htmlspecialchars($descricao_in_form, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $imgSrc = $imagem ? ('uploads/' . $imagem) : 'img/placeholder.png';
        $priceFormatted = number_format($preco, 2, ',', '.');

        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));
        $dataId = trim($slug, '-') . '-' . uniqid();

        $newBlock = "<div class=\"product-item jewel-hover bg-white rounded-lg overflow-hidden shadow-md\" data-category=\"\" data-id=\"" . $dataId . "\">\n" .
            "    <div class=\"relative h-64 product-image\">\n" .
            "        <img src=\"" . $imgSrc . "\" alt=\"" . $novoNome . "\" class=\"w-full h-full object-contain p-2\">\n" .
            "    </div>\n" .
            "    <div class=\"p-6 flex flex-col flex-1\">\n" .
            "        <h3 class=\"title-font text-xl font-bold text-gray-800 mb-2\">" . $novoNome . "</h3>\n" .
            "        <p class=\"text-gray-600 mb-4\">" . $novoDesc . "</p>\n" .
            "        <div class=\"flex justify-between items-center mb-4\">\n" .
            "            <span class=\"text-yellow-600 font-bold text-lg\">R$ " . $priceFormatted . "</span>\n" .
            "            <div class=\"flex text-yellow-400\">\n" .
            "                <i class=\"fas fa-star\"></i>\n" .
            "                <i class=\"fas fa-star\"></i>\n" .
            "                <i class=\"fas fa-star\"></i>\n" .
            "                <i class=\"fas fa-star\"></i>\n" .
            "                <i class=\"far fa-star\"></i>\n" .
            "            </div>\n" .
            "        </div>\n" .
            "        <button class=\"add-to-cart mt-auto w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300\" data-product=\"" . $dataId . "\" data-price=\"" . intval($preco) . "\">Adicionar ao Carrinho</button>\n" .
            "    </div>\n" .
            "</div>\n";

        $colecoesPath = 'colecoes.html';
        if (file_exists($colecoesPath) && is_writable($colecoesPath)) {
            $backupPath = $colecoesPath . '.bak.' . date('YmdHis');
            copy($colecoesPath, $backupPath);

            $html = file_get_contents($colecoesPath);

            $oldNameRaw = $produto['nome'];
            $oldNameEsc = htmlspecialchars($produto['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $parts = explode('<div class="product-item', $html);
            $prefixToken = '<div class="product-item';
            if (count($parts) <= 1) {
                $parts = explode("<div class='product-item", $html);
                $prefixToken = "<div class='product-item";
            }

            $head = array_shift($parts);
            $found = false;
            $newContent = $head;
            foreach ($parts as $part) {
                $segment = $prefixToken . $part;
                if (!$found && (strpos($segment, $oldNameRaw) !== false || strpos($segment, $oldNameEsc) !== false)) {
                    $newContent .= $newBlock;
                    $found = true;
                } else {
                    $newContent .= $segment;
                }
            }

            if ($found) {
                $fp = fopen($colecoesPath, 'c');
                if ($fp) {
                    if (flock($fp, LOCK_EX)) {
                        ftruncate($fp, 0);
                        fwrite($fp, $newContent);
                        fflush($fp);
                        flock($fp, LOCK_UN);
                    }
                    fclose($fp);
                } else {
                    file_put_contents($colecoesPath, $newContent);
                }
            }
        }

        header("Location: produtos.php?edit=success");
        exit();
    } else {
        $msg = "<p class='error'>❌ Erro ao atualizar produto: " . htmlspecialchars($stmt->error) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editar Produto - AURELLÉ</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

/* Paleta dourado suave (mesma do adicionar) */
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
  margin: 0;
}

.sidebar {
  width: 250px;
  background-color: #fff;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
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
  text-decoration: none;
  transition: all 0.3s;
  font-weight: 500;
}

.sidebar a:hover {
  background-color: #fff9e9;
  color: var(--gold-dark);
}

.sidebar a i { color: var(--gold-dark); }

.main {
  margin-left: 270px;
  padding: 2rem 4rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
}

form {
  background: var(--card-bg);
  padding: 2.5rem 3rem;
  border-radius: 16px;
  border: 1px solid var(--surface-border);
  box-shadow: 0 6px 30px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 900px;
}

h1 {
  font-family: 'Playfair Display', serif;
  color: var(--gold-dark);
  font-size: 1.9rem;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

label {
  display: block;
  font-weight: 600;
  color: var(--gold-dark);
  margin-top: 1.2rem;
  font-size: 0.95rem;
}

input, select, textarea {
  width: 100%;
  padding: 0.75rem;
  margin-top: 0.4rem;
  border: 1px solid #e8e0c8;
  border-radius: 10px;
  background-color: #fff;
  font-size: 0.95rem;
  color: #222;
}

input:focus, textarea:focus, select:focus {
  border-color: var(--gold);
  box-shadow: 0 0 6px rgba(228,200,106,0.15);
}

.row-2 { display: flex; gap: 20px; }
.row-2 > div { flex: 1; }

.actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
}

.btn-save {
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  color: white;
  border: none;
  padding: 0.9rem 1.8rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: transform .18s ease, box-shadow .18s;
}
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(202,166,59,0.16); }

.btn-back {
  background: #eee;
  color: #333;
  padding: 0.8rem 1.4rem;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 500;
  transition: background .2s;
}
.btn-back:hover { background: #e4e4e4; }

.field-toggle {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 12px;
  margin-top: 1.4rem;
}

.switch { --w:44px; --h:26px; display:inline-block; position:relative; }
.switch input { display:none; }
.switch .track {
  width:var(--w); height:var(--h);
  border-radius:999px; background:#ebe7df;
  display:inline-block; position:relative;
  transition: background .18s ease;
}
.switch .track::after {
  content:''; position:absolute; left:4px; top:50%;
  transform:translateY(-50%); width:18px; height:18px;
  background:white; border-radius:50%;
  transition: left .18s ease;
}
.switch input:checked + .track {
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
}
.switch input:checked + .track::after {
  left: calc(var(--w) - 4px - 18px);
}

.helper { font-size: 0.88rem; color: var(--muted); margin-top: 6px; }
.success, .error {
  padding: 0.8rem 1rem;
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

.preview {
    width: 160px;
    height: 160px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid #f3e6c0;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    margin-top: 10px;
}

.remove-btn {
    background-color: #e74c3c;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: 10px;
    transition: 0.3s;
}
.remove-btn:hover { background-color: #c0392b; }

@media (max-width:900px){
  .main { padding: 1.2rem; margin-left: 0; }
  .sidebar { display:none; }
  form { max-width: 100%; padding: 1.6rem; border-radius: 12px; }
  .row-2 { flex-direction: column; }
  .actions { justify-content: center; flex-direction: column; gap: 1rem; }
}
</style>
</head>
<body>

<aside class="sidebar">
  <h2><i class="fas fa-gem"></i> AURELLÉ</h2>
  <a href="painel.php"><i class="fas fa-home"></i> Início</a>
  <a href="produtos.php"><i class="fas fa-ring"></i> Produtos</a>
  <a href="logout_admin.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</aside>

<main class="main">
  <form method="POST" enctype="multipart/form-data">
    <h1><i class="fas fa-pen-to-square"></i> Editar Produto</h1>

    <?= $msg ?>

    <label for="nome">Nome do Produto</label>
    <input id="nome" type="text" name="nome" required value="<?= htmlspecialchars($produto['nome']) ?>">

    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" rows="4"><?= htmlspecialchars($produto['descricao']) ?></textarea>

    <div class="row-2">
      <div>
        <label for="preco">Preço (R$)</label>
        <input id="preco" type="text" name="preco" required placeholder="0,00" value="<?= number_format($produto['preco'], 2, ',', '.') ?>">
      </div>
      <div>
        <label for="estoque">Estoque</label>
        <input id="estoque" type="number" name="estoque" required value="<?= intval($produto['estoque']) ?>">
      </div>
    </div>

    <label for="categoria">Categoria</label>
    <select id="categoria" name="id_categoria" required>
      <option value="">Selecione</option>
      <?php
        // reposiciona o pointer do resultado se necessário
        if ($categorias && $categorias->num_rows > 0) {
            // re-execute query if we already consumed it (defensive)
            $categorias->data_seek(0);
        }
      ?>
      <?php while ($cat = $categorias->fetch_assoc()): ?>
        <option value="<?= $cat['id_categoria'] ?>" <?= $cat['id_categoria'] == $produto['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nome']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <label>Imagem</label>
    <input type="file" name="imagem" accept="image/*" class="border border-gray-200 p-2 rounded-md" id="imagemInput">

    <div class="img-container">
        <?php
            $imgCandidate = 'img/' . htmlspecialchars($produto['imagem']);
            $uploadCandidate = 'uploads/' . htmlspecialchars($produto['imagem']);
            if (!empty($produto['imagem']) && file_exists($imgCandidate)): ?>
                <img src="<?= $imgCandidate ?>" alt="Imagem atual" class="preview">
                <button type="submit" name="remover_imagem" class="remove-btn" onclick="return confirm('Deseja remover esta imagem?')">
                    <i class="fas fa-trash"></i> Remover imagem
                </button>
        <?php elseif (!empty($produto['imagem']) && file_exists($uploadCandidate)): ?>
                <img src="<?= $uploadCandidate ?>" alt="Imagem atual" class="preview">
                <button type="submit" name="remover_imagem" class="remove-btn" onclick="return confirm('Deseja remover esta imagem?')">
                    <i class="fas fa-trash"></i> Remover imagem
                </button>
        <?php endif; ?>
        <img id="preview" class="preview hidden mt-2" style="display:none;">
    </div>

    <div class="field-toggle">
      <div>
        <span style="font-weight:600; color:var(--gold-dark);">Destaque</span>
        <div class="helper">Marque para destacar o produto na vitrine</div>
      </div>
      <div>
        <label class="switch">
          <input type="checkbox" name="destaque" value="1" <?= $produto['destaque'] ? 'checked' : '' ?>>
          <span class="track"></span>
        </label>
      </div>
    </div>

    <div class="actions">
      <a href="produtos.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
      <button type="submit" name="salvar" class="btn-save"><i class="fas fa-save mr-2"></i>Salvar Alterações</button>
    </div>
  </form>
</main>

<script>
document.getElementById('preco').addEventListener('input', function (e) {
  let value = e.target.value.replace(/\D/g, '');
  if (!value) {
    e.target.value = '';
    return;
  }
  value = (parseInt(value) / 100).toFixed(2) + '';
  value = value.replace('.', ',');
  value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  e.target.value = value;
});

const imagemInput = document.getElementById('imagemInput');
const preview = document.getElementById('preview');

imagemInput.addEventListener('change', function(){
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e){
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>

<?php ob_end_flush(); ?>
