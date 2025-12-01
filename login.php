<?php
session_start();
require_once('config.php');

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario["senha"])) {
            $_SESSION["usuario_id"] = $usuario["id_usuario"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            header("Location: conta.html");
            exit;
        } else {
            $mensagem = "<div class='alert error'>❌ Senha incorreta!</div>";
        }
    } else {
        $mensagem = "<div class='alert error'>❌ Usuário não encontrado!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login - AURELLÉ Joalheria</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Montserrat:wght@400;500&display=swap');

:root {
    --gold-light: #d9b24d;
    --gold-hover: #c49a38;
}

body {
    font-family: 'Montserrat', sans-serif;
    background-color: #fafafa;
}

/* === ALERTAS === */
.alert {
    padding: 0.75rem;
    border-radius: 0.5rem;
    text-align: center;
    margin-bottom: 1rem;
    font-weight: 500;
}
.alert.error {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* === NAVBAR === */
.title-font {
    font-family: 'Playfair Display', serif;
    color: #000000; /* 🔧 Nome AURELLÉ agora preto */
}

/* === CORES === */
.text-gold {
    color: var(--gold-light);
}
.bg-gold {
    background-color: var(--gold-light);
}
.bg-gold:hover {
    background-color: var(--gold-hover);
}
.focus-ring {
    border-color: var(--gold-light);
    box-shadow: 0 0 0 2px rgba(217, 178, 77, 0.3);
}

/* === LINK ADMIN === */
.admin-link {
    position: fixed;
    bottom: 15px;
    right: 20px;
    font-size: 0.9rem;
    color: #9a9a9a;
    transition: color 0.2s;
}
.admin-link:hover {
    color: var(--gold-light);
}
</style>
</head>

<body class="bg-gray-50">

    <!-- === HEADER / NAVBAR === -->
    <header class="fixed w-full bg-white shadow-md z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
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
            </div>
        </div>
    </header>

    <!-- === LOGIN === -->
    <div class="flex justify-center items-center min-h-screen bg-gray-50 pt-32 px-4">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-gold mb-6 text-center">
                <i class="fas fa-gem mr-2 text-gold"></i>Login do Cliente
            </h2>

            <?= $mensagem ?>

            <form method="POST" class="space-y-4">
                <input type="email" name="email" placeholder="E-mail" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]">
                <input type="password" name="senha" placeholder="Senha" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]">
                <button type="submit"
                    class="w-full bg-gold hover:bg-[var(--gold-hover)] text-white p-3 rounded font-semibold transition">
                    Entrar
                </button>
            </form>

            <p class="text-center text-gray-600 mt-4">
                Não tem conta? <a href="cadastro.php" class="text-gold font-semibold hover:underline">Cadastre-se</a>
            </p>
        </div>
    </div>

    <!-- === LINK ADMIN DISCRETO === -->
    <a href="admin_login.php" class="admin-link"><i class="fas fa-lock mr-1"></i> Acesso administrativo</a>

</body>
</html>
