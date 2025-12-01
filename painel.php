<?php
session_start();

// Verifica se o admin está logado
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_nome'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Painel Administrativo - AURELLÉ Joalheria</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Montserrat:wght@400;500&display=swap');

:root {
  --gold: #e4c86a;
  --gold-dark: #caa63b;
  --gold-light: #f8f3e6;
}

body {
    font-family: 'Montserrat', sans-serif;
    background: linear-gradient(to bottom right, #fffdf8, #fdfaf3);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* NAVBAR */
.navbar {
    background-color: #ffffff;
    border-bottom: 1px solid var(--gold-light);
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 50;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.logo {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: var(--gold-dark);
    font-size: 1.8rem;
    text-transform: uppercase;
}

.logo:hover {
    color: var(--gold);
}

/* PAINEL */
main {
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 7rem 1rem 3rem;
}

.painel-container {
    background-color: #ffffff;
    padding: 3rem 2rem;
    border-radius: 1rem;
    border: 1px solid var(--gold-light);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    text-align: center;
    max-width: 600px;
    width: 100%;
}

h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: var(--gold-dark);
    margin-bottom: 2rem;
}

.botoes {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.botoes a {
    background: linear-gradient(to right, var(--gold), var(--gold-dark));
    color: white;
    padding: 0.9rem;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.botoes a:hover {
    background: linear-gradient(to right, var(--gold-dark), var(--gold));
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(202, 166, 59, 0.4);
}

/* FOOTER */
footer {
    text-align: center;
    color: #9ca3af;
    padding: 1rem;
    font-size: 0.9rem;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="container mx-auto px-8 py-4 flex justify-between items-center">
    <a href="index.html" class="logo">AURELLÉ</a>

    <nav class="hidden md:flex space-x-8">
      <a href="index.html" class="text-gray-700 hover:text-yellow-600 font-medium">Início</a>
      <a href="colecoes.html" class="text-gray-700 hover:text-yellow-600 font-medium">Coleções</a>
      <a href="relogios.html" class="text-gray-700 hover:text-yellow-600 font-medium">Relógios</a>
      <a href="sobre.html" class="text-gray-700 hover:text-yellow-600 font-medium">Sobre Nós</a>
      <a href="contato.php" class="text-gray-700 hover:text-yellow-600 font-medium">Contato</a>
    </nav>

    <div class="flex items-center space-x-5 text-gray-700">
      <a href="#" class="hover:text-yellow-600"><i class="fas fa-search"></i></a>
      <a href="admin_perfil.php" class="hover:text-yellow-600"><i class="fas fa-user-shield"></i></a>
      <a href="logout.php" class="hover:text-yellow-600"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</header>

<!-- CONTEÚDO -->
<main>
  <div class="painel-container">
      <h1>Bem-vindo(a), <?= htmlspecialchars($_SESSION['admin_nome']); ?> <i class="fas fa-gem"></i></h1>

      <div class="botoes">
          <a href="adicionar_produtos.php"><i class="fas fa-plus-circle"></i> Adicionar Produtos</a>
          <a href="editar_produto.php"><i class="fas fa-edit"></i> Editar Produtos</a>
          <a href="excluir_produtos.php"><i class="fas fa-trash"></i> Excluir Produtos</a>
       <a href="mensagens.php"><i class="fas fa-plus-circle"></i>Mensagens</a>
        </div>
  </div>
</main>

<footer>
  © <?= date('Y'); ?> AURELLÉ Joalheria — Painel Administrativo
</footer>

</body>
</html>