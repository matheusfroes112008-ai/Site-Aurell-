<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}


$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
$msg = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval(str_replace(',', '.', str_replace('.', '', $_POST['preco']))); // ✅ trata vírgula
    $estoque = intval($_POST['estoque']);
    $id_categoria = intval($_POST['id_categoria']);
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $imagem = null;

    
    if (!empty($_FILES['imagem']['name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nomeImagem = uniqid('produto_', true) . '.' . $ext;
        $imagemPath = $uploadDir . $nomeImagem;

        
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidos)) {
            $msg = "<p class='error'>Formato inválido! Envie JPG, PNG ou WEBP.</p>";
        } elseif (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath)) {
            $imagem = $nomeImagem;
        } else {
            $msg = "<p class='error'>Erro ao enviar a imagem.</p>";
        }
    }

    if (!$msg) {
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, estoque, id_categoria, destaque, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiss", $nome, $descricao, $preco, $estoque, $id_categoria, $destaque, $imagem);

    if ($stmt->execute()) {
      $msg = "<p class='success'>✅ Produto cadastrado com sucesso!</p>";

      // --- Adiciona também o produto em colecoes.html ---
      // Monta os dados seguros
      $produtoNome = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $produtoDesc = htmlspecialchars($descricao, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      // imagem: usa upload se existir, senão um placeholder relativo
      $imgSrc = $imagem ? ('uploads/' . $imagem) : 'img/placeholder.png';
      // formata preço para display (R$ 1.234,56)
      $priceFormatted = number_format($preco, 2, ',', '.');

      // pega nome da categoria para data-category (tenta leitura do DB)
      $dataCategory = '';
      $catQ = $conn->prepare("SELECT nome FROM categorias WHERE id_categoria = ? LIMIT 1");
      if ($catQ) {
        $catQ->bind_param('i', $id_categoria);
        if ($catQ->execute()) {
          $res = $catQ->get_result();
          if ($row = $res->fetch_assoc()) {
            // normaliza para classes: tudo minúsculo e sem espaços
            $dataCategory = preg_replace('/[^a-z0-9]+/i', ' ', $row['nome']);
            $dataCategory = strtolower(trim(str_replace(' ', ' ', $dataCategory)));
          }
        }
      }

      // cria um id único legível para o item
      $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));
      $dataId = trim($slug, '-') . '-' . uniqid();

      // monta o bloco HTML do produto (simples e compatível com o layout existente)
      $productHtml = "\n                <div class=\"product-item jewel-hover bg-white rounded-lg overflow-hidden shadow-md\" data-category=\"" . $dataCategory . "\" data-id=\"" . $dataId . "\">\n                    <div class=\"relative h-64 product-image\">\n                        <img src=\"" . $imgSrc . "\" alt=\"" . $produtoNome . "\" class=\"w-full h-full object-contain p-2\">\n                    </div>\n                    <div class=\"p-6 flex flex-col flex-1\">\n                        <h3 class=\"title-font text-xl font-bold text-gray-800 mb-2\">" . $produtoNome . "</h3>\n                        <p class=\"text-gray-600 mb-4\">" . $produtoDesc . "</p>\n                        <div class=\"flex justify-between items-center mb-4\">\n                            <span class=\"text-yellow-600 font-bold text-lg\">R$ " . $priceFormatted . "</span>\n                            <div class=\"flex text-yellow-400\">\n                                <i class=\"fas fa-star\"></i>\n                                <i class=\"fas fa-star\"></i>\n                                <i class=\"fas fa-star\"></i>\n                                <i class=\"fas fa-star\"></i>\n                                <i class=\"far fa-star\"></i>\n                            </div>\n                        </div>\n                        <button class=\"add-to-cart mt-auto w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300\" data-product=\"" . $dataId . "\" data-price=\"" . intval($preco) . "\">Adicionar ao Carrinho</button>\n                    </div>\n                </div>\n            ";

      // insere o HTML logo após o comentário que indica onde itens admin devem ser injetados
      $colecoesPath = 'colecoes.html';
      if (file_exists($colecoesPath) && is_writable($colecoesPath)) {
        $colecoesContent = file_get_contents($colecoesPath);
        $marker = '<!-- Itens de admin (localStorage) serão injetados acima -->';
        if (strpos($colecoesContent, $marker) !== false) {
          // insere o bloco logo depois do marker
          $colecoesContent = str_replace($marker, $marker . "\n" . $productHtml, $colecoesContent);
          file_put_contents($colecoesPath, $colecoesContent);
        } else {
          // se não encontrar o marker, apenas anexa ao container #products-grid
          $insertAfter = '<div class="product-grid" id="products-grid">';
          $pos = strpos($colecoesContent, $insertAfter);
          if ($pos !== false) {
            $posEnd = $pos + strlen($insertAfter);
            $colecoesContent = substr_replace($colecoesContent, $insertAfter . "\n" . $productHtml, $pos, strlen($insertAfter));
            file_put_contents($colecoesPath, $colecoesContent);
          } else {
            // fallback: append ao final do arquivo
            file_put_contents($colecoesPath, $colecoesContent . "\n" . $productHtml);
          }
        }
      }

    } else {
      $msg = "<p class='error'>❌ Erro ao cadastrar produto.</p>";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Adicionar Produto | Painel AURELLÉ</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

/* Paleta dourado suave */
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
  <form action="" method="POST" enctype="multipart/form-data">
    <h1><i class="fas fa-plus"></i> Adicionar Produto</h1>

    <?= $msg ?>

    <label for="nome">Nome do Produto</label>
    <input id="nome" type="text" name="nome" required>

    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao" rows="4"></textarea>

    <div class="row-2">
      <div>
        <label for="preco">Preço (R$)</label>
        <input id="preco" type="text" name="preco" required placeholder="0,00">
      </div>
      <div>
        <label for="estoque">Estoque</label>
        <input id="estoque" type="number" name="estoque" required>
      </div>
    </div>

    <label for="categoria">Categoria</label>
    <select id="categoria" name="id_categoria" required>
      <option value="">Selecione</option>
      <?php while ($cat = $categorias->fetch_assoc()): ?>
        <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
      <?php endwhile; ?>
    </select>

    <label>Imagem</label>
    <input type="file" name="imagem" accept="image/*" class="border border-gray-200 p-2 rounded-md">

    <div class="field-toggle">
      <div>
        <span style="font-weight:600; color:var(--gold-dark);">Destaque</span>
        <div class="helper">Marque para destacar o produto na vitrine</div>
      </div>
      <div>
        <label class="switch">
          <input type="checkbox" name="destaque" value="1">
          <span class="track"></span>
        </label>
      </div>
    </div>

    <div class="actions">
      <a href="produtos.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
      <button type="submit" class="btn-save"><i class="fas fa-save mr-2"></i>Salvar Produto</button>
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
</script>

</body>
</html>
