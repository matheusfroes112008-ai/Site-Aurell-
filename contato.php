<?php
// Processar formulário se for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    
    // Dados de conexão
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "aurelle";
    
    // Conectar ao banco
    $conn = new mysqli($host, $user, $password, $database);
    
    if (!$conn->connect_error) {
        // Coletar dados
        $nome = trim($_POST['nome']) . ' ' . trim($_POST['sobrenome']);
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone'] ?? '');
        $assunto = trim($_POST['assunto']);
        $mensagem = trim($_POST['mensagem']);
        
        // Validar
        if (!empty($nome) && !empty($email) && !empty($assunto) && !empty($mensagem)) {
            // Inserir no banco
            $sql = "INSERT INTO mensagens_contato (nome, email, telefone, assunto, mensagem, lida) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sssss", $nome, $email, $telefone, $assunto, $mensagem);
                if ($stmt->execute()) {
                    $mensagem_sucesso = "Mensagem enviada com sucesso! Entraremos em contato em breve.";
                } else {
                    $mensagem_erro = "Erro ao enviar mensagem. Tente novamente.";
                }
                $stmt->close();
            }
        } else {
            $mensagem_erro = "Por favor, preencha todos os campos obrigatórios.";
        }
        $conn->close();
    } else {
        $mensagem_erro = "Erro de conexão com o banco de dados.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - AURELLÉ Joalheria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap');
        
        body {
            font-family: 'Montserrat', sans-serif;
            scroll-behavior: smooth;
        }
        
        .title-font {
            font-family: 'Playfair Display', serif;
        }

        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .mobile-menu.open {
            transform: translateX(0);
        }

        .contact-card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .form-input:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
        }

        .map-container {
            height: 400px;
            background: #f3f4f6;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header/Navbar -->
    <header class="fixed w-full bg-white shadow-md z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.html" class="title-font text-2xl font-bold text-gray-800">AURELLÉ</a>
            </div>
            
            <nav class="hidden md:flex space-x-8">
                <a href="index.html" class="text-gray-700 hover:text-yellow-600 font-medium">Início</a>
                <a href="colecoes.html" class="text-gray-700 hover:text-yellow-600 font-medium">Coleções</a>
                <a href="relogios.html" class="text-gray-700 hover:text-yellow-600 font-medium">Relógios</a>
                <a href="sobre.html" class="text-gray-700 hover:text-yellow-600 font-medium">Sobre Nós</a>
                <a href="contato.php" class="text-yellow-600 font-medium">Contato</a>
            </nav>
            
            <div class="flex items-center space-x-4">
                <a href="javascript:void(0)" id="search-button" class="text-gray-700 hover:text-yellow-600"><i class="fas fa-search"></i></a>
                <a href="conta.html" class="text-gray-700 hover:text-yellow-600"><i class="fas fa-user"></i></a>
                <a href="carrinho.html" class="text-gray-700 hover:text-yellow-600 relative">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="absolute -top-2 -right-2 bg-yellow-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="cart-count">0</span>
                </a>
                <button class="md:hidden text-gray-700" id="mobile-menu-button">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="md:hidden mobile-menu fixed top-16 left-0 w-full h-screen bg-white shadow-md" id="mobile-menu">
            <div class="p-4">
                <a href="index.html" class="block py-3 text-gray-700 hover:text-yellow-600 border-b border-gray-200">Início</a>
                <a href="colecoes.html" class="block py-3 text-gray-700 hover:text-yellow-600 border-b border-gray-200">Coleções</a>
                <a href="relogios.html" class="block py-3 text-gray-700 hover:text-yellow-600 border-b border-gray-200">Relógios</a>
                <a href="sobre.html" class="block py-3 text-gray-700 hover:text-yellow-600 border-b border-gray-200">Sobre Nós</a>
                <a href="contato.php" class="block py-3 text-yellow-600 border-b border-gray-200">Contato</a>
                <a href="conta.html" class="block py-3 text-gray-700 hover:text-yellow-600 border-b border-gray-200">Minha Conta</a>
                <a href="carrinho.html" class="block py-3 text-gray-700 hover:text-yellow-600">Carrinho</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-yellow-600 to-yellow-700 text-white py-20 pt-24">
        <div class="container mx-auto px-4 text-center">
            <h1 class="title-font text-3xl sm:text-4xl md:text-5xl font-bold mb-6">Entre em Contato</h1>
            <p class="text-lg sm:text-xl max-w-2xl mx-auto">Estamos aqui para ajudar. Visite nossa loja física, ligue ou envie uma mensagem.</p>
            
            <?php if (isset($mensagem_sucesso)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto mt-6">
                <?php echo $mensagem_sucesso; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($mensagem_erro)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 max-w-2xl mx-auto mt-6">
                <?php echo $mensagem_erro; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="contact-card bg-white rounded-lg p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-map-marker-alt text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="title-font text-xl font-bold text-gray-800 mb-4">Nossa Loja</h3>
                    <p class="text-gray-600 mb-2">Av. Paulista, 1000</p>
                    <p class="text-gray-600 mb-2">Bela Vista, São Paulo - SP</p>
                    <p class="text-gray-600">CEP: 01310-100</p>
                </div>
                
                <div class="contact-card bg-white rounded-lg p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-phone text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="title-font text-xl font-bold text-gray-800 mb-4">Telefone</h3>
                    <p class="text-gray-600 mb-2">(11) 1234-5678</p>
                    <p class="text-gray-600 mb-2">(11) 98765-4321</p>
                    <p class="text-gray-600">WhatsApp disponível</p>
                </div>
                
                <div class="contact-card bg-white rounded-lg p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-envelope text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="title-font text-xl font-bold text-gray-800 mb-4">E-mail</h3>
                    <p class="text-gray-600 mb-2">contato@aurelle.com.br</p>
                    <p class="text-gray-600 mb-2">vendas@aurelle.com.br</p>
                    <p class="text-gray-600">suporte@aurelle.com.br</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Hours Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="title-font text-3xl md:text-4xl font-bold text-gray-800 mb-4">Horário de Funcionamento</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Visite-nos nos horários de funcionamento ou agende um horário especial</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <h3 class="title-font text-xl font-bold text-gray-800 mb-6">Loja Física</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Segunda a Sexta</span>
                                <span class="text-gray-600">10h às 19h</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Sábado</span>
                                <span class="text-gray-600">10h às 16h</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Domingo</span>
                                <span class="text-gray-600">Fechado</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="font-medium">Feriados</span>
                                <span class="text-gray-600">Consulte</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <h3 class="title-font text-xl font-bold text-gray-800 mb-6">Atendimento Online</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Segunda a Sexta</span>
                                <span class="text-gray-600">8h às 20h</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Sábado</span>
                                <span class="text-gray-600">9h às 17h</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="font-medium">Domingo</span>
                                <span class="text-gray-600">10h às 16h</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="font-medium">Chat 24/7</span>
                                <span class="text-green-600">Disponível</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="title-font text-3xl md:text-4xl font-bold text-gray-800 mb-4">Envie uma Mensagem</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Preencha o formulário abaixo e entraremos em contato em até 24 horas</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div>
                        <form class="space-y-6" id="contact-form" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nome" class="block text-gray-700 font-medium mb-2">Nome *</label>
                                    <input type="text" id="nome" name="nome" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300" required>
                                </div>
                                <div>
                                    <label for="sobrenome" class="block text-gray-700 font-medium mb-2">Sobrenome *</label>
                                    <input type="text" id="sobrenome" name="sobrenome" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300" required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-gray-700 font-medium mb-2">E-mail *</label>
                                <input type="email" id="email" name="email" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300" required>
                            </div>
                            
                            <div>
                                <label for="telefone" class="block text-gray-700 font-medium mb-2">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300">
                            </div>
                            
                            <div>
                                <label for="assunto" class="block text-gray-700 font-medium mb-2">Assunto *</label>
                                <select id="assunto" name="assunto" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300" required>
                                    <option value="">Selecione um assunto</option>
                                    <option value="Consulta sobre Produtos">Consulta sobre Produtos</option>
                                    <option value="Orçamento Personalizado">Orçamento Personalizado</option>
                                    <option value="Manutenção de Joias">Manutenção de Joias</option>
                                    <option value="Avaliação de Gemas">Avaliação de Gemas</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="mensagem" class="block text-gray-700 font-medium mb-2">Mensagem *</label>
                                <textarea id="mensagem" name="mensagem" rows="5" class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition duration-300" placeholder="Descreva sua solicitação..." required></textarea>
                            </div>
                            
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" id="newsletter" name="newsletter" class="mt-1">
                                <label for="newsletter" class="text-gray-600 text-sm">Desejo receber novidades e ofertas exclusivas por e-mail</label>
                            </div>
                            
                            <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-4 px-6 rounded-lg transition duration-300 text-lg">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Enviar Mensagem
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <div class="bg-gray-50 rounded-lg p-8 h-full">
                            <h3 class="title-font text-xl font-bold text-gray-800 mb-6">Informações Adicionais</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3">Agendamento de Visita</h4>
                                    <p class="text-gray-600 text-sm mb-3">Para uma experiência personalizada, agende uma visita em nossa loja. Nossos especialistas estarão disponíveis para:</p>
                                    <ul class="text-gray-600 text-sm space-y-1 ml-4">
                                        <li>• Apresentar nossas coleções</li>
                                        <li>• Realizar avaliações de gemas</li>
                                        <li>• Criar designs personalizados</li>
                                        <li>• Oferecer consultoria especializada</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3">Atendimento VIP</h4>
                                    <p class="text-gray-600 text-sm">Clientes VIP têm acesso a:</p>
                                    <ul class="text-gray-600 text-sm space-y-1 ml-4">
                                        <li>• Horários especiais de atendimento</li>
                                        <li>• Lançamentos exclusivos</li>
                                        <li>• Eventos privados</li>
                                        <li>• Descontos especiais</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-3">Emergências</h4>
                                    <p class="text-gray-600 text-sm">Para situações urgentes, ligue para:</p>
                                    <p class="text-yellow-600 font-bold">(11) 99999-9999</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="title-font text-3xl md:text-4xl font-bold text-gray-800 mb-4">Nossa Localização</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Visite nossa loja no coração de São Paulo</p>
            </div>
            
            <div class="max-w-6xl mx-auto">
                <div class="map-container">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-6xl mb-4 text-gray-400"></i>
                        <p class="text-lg font-medium">Mapa Interativo</p>
                        <p class="text-sm text-gray-500">Av. Paulista, 1000 - São Paulo, SP</p>
                        <a href="https://maps.google.com" target="_blank" class="inline-block mt-4 bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                            Ver no Google Maps
                        </a>
                    </div>
                </div>
                
                <div class="mt-8 text-center">
                    <h3 class="title-font text-xl font-bold text-gray-800 mb-4">Como Chegar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <i class="fas fa-subway text-3xl text-yellow-600 mb-4"></i>
                            <h4 class="font-bold text-gray-800 mb-2">Metrô</h4>
                            <p class="text-gray-600 text-sm">Estação Trianon-Masp (Linha Verde)</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <i class="fas fa-bus text-3xl text-yellow-600 mb-4"></i>
                            <h4 class="font-bold text-gray-800 mb-2">Ônibus</h4>
                            <p class="text-gray-600 text-sm">Várias linhas passam pela Av. Paulista</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <i class="fas fa-car text-3xl text-yellow-600 mb-4"></i>
                            <h4 class="font-bold text-gray-800 mb-2">Carro</h4>
                            <p class="text-gray-600 text-sm">Estacionamento disponível no local</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="title-font text-3xl md:text-4xl font-bold text-gray-800 mb-4">Perguntas Frequentes</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Respostas para as dúvidas mais comuns dos nossos clientes</p>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-2">Como agendar uma consulta personalizada?</h3>
                        <p class="text-gray-600">Entre em contato conosco pelo telefone, e-mail ou preencha o formulário acima. Nossa equipe entrará em contato para agendar o melhor horário.</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-2">Vocês fazem manutenção de joias de outras marcas?</h3>
                        <p class="text-gray-600">Sim! Oferecemos serviços de manutenção para joias de qualquer marca, sempre com a mesma qualidade e cuidado.</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-2">Quanto tempo leva para criar uma peça personalizada?</h3>
                        <p class="text-gray-600">O prazo varia de acordo com a complexidade da peça. Peças simples levam de 2 a 4 semanas, enquanto designs mais elaborados podem levar de 6 a 12 semanas.</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-2">Vocês oferecem garantia nas peças?</h3>
                        <p class="text-gray-600">Sim! Todas as nossas peças possuem garantia de 2 anos contra defeitos de fabricação, além de certificado de autenticidade.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Overlay -->
    <div id="search-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-start justify-center pt-24 z-50">
        <div class="bg-white w-11/12 max-w-2xl rounded-lg p-4 shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-search text-gray-500 mr-3"></i>
                <input id="site-search-input" type="text" placeholder="Buscar produtos..." class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-600">
                <button id="search-close" class="ml-3 text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-yellow-600 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="title-font text-xl font-bold mb-4">AURELLÉ</h3>
                    <p class="text-yellow-100 text-sm">Joalheria finissima desde 1985. Criando peças que se tornam heranças familiares.</p>
                    <div class="flex mt-6 space-x-4">
                        <a href="#" class="text-yellow-100 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-yellow-100 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-yellow-100 hover:text-white"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="title-font font-bold mb-4">Links Rápidos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.html" class="text-yellow-100 hover:text-white">Início</a></li>
                        <li><a href="colecoes.html" class="text-yellow-100 hover:text-white">Coleções</a></li>
                        <li><a href="sobre.html" class="text-yellow-100 hover:text-white">Sobre Nós</a></li>
                        <li><a href="contato.php" class="text-yellow-100 hover:text-white">Contato</a></li>
                        <li><a href="admin_login.php" class="text-yellow-100 hover:text-white">Admin</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="title-font font-bold mb-4">Serviços</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-yellow-100 hover:text-white">Design Personalizado</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Manutenção de Joias</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Avaliação de Gemas</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Presentes Corporativos</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="title-font font-bold mb-4">Informações</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-yellow-100 hover:text-white">Política de Privacidade</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Termos de Serviço</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Trocas e Devoluções</a></li>
                        <li><a href="#" class="text-yellow-100 hover:text-white">Perguntas Frequentes</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-yellow-700 mt-12 pt-8 text-center text-yellow-100 text-sm">
                <p>&copy; 2024 AURELLÉ Joalheria. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Update cart count
        function updateCartCount() {
            const cartCount = document.getElementById('cart-count');
            cartCount.textContent = cart.length;
        }

        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                mobileMenu.classList.remove('open');
            }
        });

        // Initialize cart count
        updateCartCount();

        // Search overlay
        (function() {
            const openBtn = document.getElementById('search-button');
            const overlay = document.getElementById('search-overlay');
            const closeBtn = document.getElementById('search-close');
            const input = document.getElementById('site-search-input');

            function openOverlay() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                setTimeout(() => input && input.focus(), 50);
            }
            function closeOverlay() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
            if (openBtn && overlay) {
                openBtn.addEventListener('click', openOverlay);
            }
            if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
            overlay && overlay.addEventListener('click', (e) => { if (e.target === overlay) closeOverlay(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeOverlay(); });
            input && input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    const q = input.value.trim();
                    if (q) {
                        window.location.href = `colecoes.html?q=${encodeURIComponent(q)}`;
                    }
                }
            });
        })();
    </script>
</body>
</html>