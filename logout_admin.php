<?php
session_start();

// Se tiver sessão de admin, encerra
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nome']);
session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#fdfaf3] flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-xl rounded-3xl p-10 text-center w-[450px] border border-[#f0e6d2]">

        <h1 class="text-3xl font-semibold text-[#c2973e] mb-4">
            Sessão encerrada
        </h1>

        <p class="text-gray-600 mb-8 text-lg">
            Você saiu da área administrativa com sucesso.
        </p>

        <!-- Ícone -->
        <div class="text-[#c2973e] text-6xl mb-8">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>

        <!-- Botão de login -->
        <a href="admin_login.php"
           class="block bg-[#caa74a] hover:bg-[#b08f3f] transition text-white font-medium py-3 rounded-lg shadow-md">
            Voltar ao Login
        </a>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
