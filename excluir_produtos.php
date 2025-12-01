<?php
require 'config.php';
session_start();

// Verifica se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produtos.php?erro=invalid_id");
    exit();
}

$id = intval($_GET['id']);

// Busca o produto
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id_produto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: produtos.php?erro=notfound");
    exit();
}

$produto = $result->fetch_assoc();

// mensagem de feedback (segue o padrão de adicionar_produtos)
$msg = '';

// Se o usuário confirmou exclusão
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $erro = false;

    // remove imagem se existir
    $caminhoImagem = "uploads/" . $produto['imagem'];
    if (!empty($produto['imagem']) && file_exists($caminhoImagem)) {
        if (!@unlink($caminhoImagem)) {
            // não é fatal, apenas marca erro
            $erro = true;
        }
    }

    // deleta do banco
    $delete = $conn->prepare("DELETE FROM produtos WHERE id_produto = ?");
    $delete->bind_param("i", $id);
    if (!$delete->execute()) {
        $erro = true;
    }

    // --- Remover o card correspondente em colecoes.html ---
    $colecoesPath = 'colecoes.html';
    if (file_exists($colecoesPath) && is_writable($colecoesPath)) {
        // cria backup antes de alterar
        $backupPath = $colecoesPath . '.bak.' . date('YmdHis');
        copy($colecoesPath, $backupPath);

        $html = file_get_contents($colecoesPath);

        // formas de procurar: o nome original e a versão escapa
        $nomeRaw = $produto['nome'];
        $nomeEsc = htmlspecialchars($produto['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // explode por blocos de product-item para remoção segura
        $parts = explode('<div class="product-item', $html);
        // se não encontrou com essa assinatura, tenta sem escape (caso atributos estejam em aspas simples)
        if (count($parts) <= 1) {
            $parts = explode("<div class='product-item", $html);
            $prefixToken = "<div class='product-item";
        } else {
            $prefixToken = '<div class="product-item';
        }

        // reconstrói o conteúdo sem o bloco que contenha o nome do produto
        $result = array_shift($parts); // parte antes do primeiro product-item
        foreach ($parts as $part) {
            $segment = $prefixToken . $part;
            // procura pelas duas formas de nome no segmento
            if (strpos($segment, $nomeRaw) !== false || strpos($segment, $nomeEsc) !== false) {
                // pular este segmento (remove o card)
                continue;
            }
            $result .= $segment;
        }

        // escreve de volta somente se houve remoção (compara tamanho)
        if (strlen($result) !== strlen($html)) {
            // atenção a concorrência: usa um lock simples ao escrever
            $fp = fopen($colecoesPath, 'c');
            if ($fp) {
                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, 0);
                    fwrite($fp, $result);
                    fflush($fp);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            } else {
                // fallback sem lock
                file_put_contents($colecoesPath, $result);
            }
        }
    }

    if ($erro) {
        $msg = "<p class='error'>❌ Ocorreu um problema ao excluir o produto. Verifique os logs e tente novamente.</p>";
    } else {
        $msg = "<p class='success'>✅ Produto excluído com sucesso!</p>";
        // produto removido, evita exibir o cartão abaixo
        $produto = null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Produto | AURELLÉ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&display=swap');

        :root {
            --gold: #e4c86a;
            --gold-dark: #caa63b;
            --gold-light: #f8f3e6;
            --surface-border: #f2e6c6;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to bottom right, #fffdf8, #fdfaf3);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
        }

        .container {
            background: #fffefb;
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid var(--surface-border);
            box-shadow: 0 6px 30px rgba(0,0,0,0.05);
            max-width: 440px;
            width: 100%;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            color: var(--gold-dark);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .warning-text {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .product-card {
            background: var(--gold-light);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--gold);
            margin-bottom: 2rem;
            text-align: center;
        }

        .product-name {
            font-weight: 600;
            color: var(--gold-dark);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--surface-border);
            margin: 0 auto 1rem;
        }

        .no-image {
            color: #999;
            font-style: italic;
            margin: 1rem 0;
        }

        .product-price {
            font-weight: 600;
            color: var(--gold-dark);
            font-size: 1.1rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.9rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .success, .error {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }
        .success { background: #fdf9ea; border: 1px solid #e4c86a; color: #caa63b; }
        .error { background: #ffeaea; border: 1px solid #ffb6b6; color: #b20000; }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .btn-cancel {
            background: transparent;
            border: 2px solid #e0e0e0;
            color: #666;
        }

        .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #d0d0d0;
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem 1.5rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <?= $msg ?>
        <h1>
            <i class="fas fa-exclamation-triangle"></i>
            Excluir Produto
        </h1>

        <p class="warning-text">Tem certeza de que deseja excluir o produto abaixo? Esta ação não pode ser desfeita.</p>

        <?php if (!empty($produto)): ?>
            <div class="product-card">
                <div class="product-name"><?= htmlspecialchars($produto['nome']); ?></div>
                
                <?php if (!empty($produto['imagem']) && file_exists("uploads/" . $produto['imagem'])): ?>
                    <img src="uploads/<?= htmlspecialchars($produto['imagem']); ?>" 
                         alt="<?= htmlspecialchars($produto['nome']); ?>" 
                         class="product-image">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-image"></i> Sem imagem
                    </div>
                <?php endif; ?>
                
                <div class="product-price">
                    R$ <?= number_format($produto['preco'], 2, ',', '.'); ?>
                </div>
            </div>

            <form method="POST" class="actions">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Sim, excluir
                </button>
                <a href="produtos.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </form>
        <?php else: ?>
            <div style="text-align:center; margin-top:1rem;">
                <a href="produtos.php" class="btn btn-cancel">Voltar aos produtos</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>