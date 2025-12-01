<?php
require_once('config.php');
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha);

    if ($stmt->execute()) {
        $mensagem = "<div class='alert success'>✅ Cadastro realizado com sucesso! Faça login.</div>";
    } else {
        $mensagem = "<div class='alert error'>❌ E-mail já cadastrado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastro - AURELLÉ Joalheria</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Montserrat:wght@400;500&display=swap');

body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f9f9f8;
}

/* === ALERTAS === */
.alert {
    padding: 0.75rem;
    border-radius: 0.5rem;
    text-align: center;
    margin-bottom: 1rem;
    font-weight: 500;
}
.alert.success {
    background-color: #ecfdf5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}
.alert.error {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* === CORES E ESTILO GLOBAL === */
.title-font {
    font-family: 'Playfair Display', serif;
    color: #000000; /* 🔧 AURELLÉ agora em preto */
}
.text-gold {
    color: #dab44f;
}
.bg-gold {
    background-color: #dab44f;
}
.bg-gold:hover {
    background-color: #e3c15a;
}
.focus-ring {
    border-color: #dab44f;
    box-shadow: 0 0 0 2px rgba(218,180,79,0.25);
}

/* === CARTÃO CENTRAL === */
.card {
    background-color: #ffffff;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 6px 15px rgba(0,0,0,0.05);
    width: 100%;
    max-width: 400px;
}
</style>
</head>

<body class="bg-gray-50">
   
<header class="fixed w-full bg-white shadow-md z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- 🔧 AURELLÉ agora aparece em preto -->
        <a href="index.html" class="title-font text-2xl font-bold">AURELLÉ</a>
        
        <nav class="hidden md:flex space-x-8">
            <a href="index.html" class="text-gray-700 hover:text-gold font-medium">Início</a>
            <a href="colecoes.html" class="text-gray-700 hover:text-gold font-medium">Coleções</a>
            <a href="relogios.html" class="text-gray-700 hover:text-gold font-medium">Relógios</a>
            <a href="sobre.html" class="text-gray-700 hover:text-gold font-medium">Sobre Nós</a>
            <a href="contato.html" class="text-gray-700 hover:text-gold font-medium">Contato</a>
        </nav>
        
        <div class="flex items-center space-x-4">
            <a href="#" class="text-gray-700 hover:text-gold"><i class="fas fa-search"></i></a>
            <a href="login.php" class="text-gold"><i class="fas fa-user"></i></a>
            <a href="carrinho.html" class="text-gray-700 hover:text-gold relative">
                <i class="fas fa-shopping-bag"></i>
                <span class="absolute -top-2 -right-2 bg-gold text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
            </a>
            <button class="md:hidden text-gray-700"><i class="fas fa-bars text-xl"></i></button>
        </div>
    </div>
</header>

<!-- === CONTEÚDO CENTRAL === -->
<div class="flex justify-center items-center min-h-screen bg-gray-50 pt-32 px-4">
    <div class="card">
        <h2 class="text-2xl font-bold text-gold mb-6 text-center">
            <i class="fas fa-gem mr-2 text-gold"></i>Cadastro
        </h2>
        <?= $mensagem ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="nome" placeholder="Nome completo" required 
                class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[#dab44f]">
            <input type="email" name="email" placeholder="E-mail" required 
                class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[#dab44f]">
            <input type="password" name="senha" placeholder="Senha" required 
                class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[#dab44f]">
            <button type="submit" 
                class="w-full bg-gold hover:bg-[#e3c15a] text-white p-3 rounded font-semibold transition">
                Cadastrar
            </button>
        </form>
        <p class="text-center text-gray-600 mt-4">
            Já tem conta? <a href="login.php" class="text-gold font-semibold hover:underline">Faça login</a>
        </p>

 </div>
</footer>
</body>
</html>
