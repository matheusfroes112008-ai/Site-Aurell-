<?php
session_start();
require 'config.php';

// Verifica se é admin logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Busca produtos + categorias
$sql = "SELECT p.*, c.nome AS categoria_nome FROM produtos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        ORDER BY p.id_produto DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Produtos | Painel AURELLÉ</title>
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

        /* BOTÕES */
        .btn-add {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(202, 166, 59, 0.3);
        }

        .btn-edit {
            background: linear-gradient(135deg, #3ba55d, #2e874b);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;

        }

        .btn-edit:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 165, 93, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }

        /* TABELA */
        .table-container {
            overflow-x: auto;
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--surface-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background-color: var(--gold-light);
            color: var(--gold-dark);
            font-weight: 600;
        }

        th,
        td {
            padding: 1.2rem;
            border-bottom: 1px solid var(--surface-border);
        }

        tbody tr {
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: var(--gold-light);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* IMAGENS */
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid var(--surface-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .no-image {
            color: var(--muted);
            font-style: italic;
            font-size: 0.9rem;
        }

        /* STATUS DESTAQUE */
        .destaque-star {
            color: var(--gold-dark);
            font-size: 1.2rem;
        }

        .destaque-empty {
            color: #ddd;
            font-size: 1.2rem;
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

            .table-container {
                border-radius: 12px;
            }

            th,
            td {
                padding: 0.5rem 0.2rem;
                font-size: 0.9rem;
            }

            .btn-edit,
            .btn-delete {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }

        .actions-cell {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            flex-direction: column;
            width: 100px;
        }

        .actions-cell a {
            display: flex;
            text-align: center;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2><i class="fas fa-gem"></i> AURELLÉ</h2>
        <a href="painel.php"><i class="fas fa-home"></i> Início</a>
        <a href="produtos.php" class="active"><i class="fas fa-ring"></i> Produtos</a>
        <a href="admin_perfil.php"><i class="fas fa-user-shield"></i> Meu Perfil</a>
        <a href="logout_admin.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </aside>

    <!-- Main -->
    <main class="main">
        <h1><i class="fas fa-box"></i> Lista de Produtos</h1>

        <a href="adicionar_produtos.php" class="btn-add">
            <i class="fas fa-plus"></i> Adicionar Produto
        </a>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Destaque</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id_produto'] ?></td>
                            <td>
                                <?php
                                $imgCandidate = "img/" . htmlspecialchars($row['imagem']);
                                $uploadCandidate = "uploads/" . htmlspecialchars($row['imagem']);
                                if (!empty($row['imagem']) && file_exists($imgCandidate)): ?>
                                    <img src="<?= $imgCandidate ?>" alt="<?= htmlspecialchars($row['nome']) ?>"
                                        class="product-image">
                                <?php elseif (!empty($row['imagem']) && file_exists($uploadCandidate)): ?>
                                    <img src="<?= $uploadCandidate ?>" alt="<?= htmlspecialchars($row['nome']) ?>"
                                        class="product-image">
                                <?php else: ?>
                                    <span class="no-image">Sem imagem</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['nome']) ?></td>
                            <td><?= htmlspecialchars($row['categoria_nome'] ?? '-') ?></td>
                            <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
                            <td><?= $row['estoque'] ?></td>
                            <td>
                                <?php if ($row['destaque']): ?>
                                    <i class="fas fa-star destaque-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star destaque-empty"></i>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="editar_produto.php?id=<?= $row['id_produto'] ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="excluir_produtos.php?id=<?= $row['id_produto'] ?>" class="btn-delete"
                                    onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>