<?php
// pages_data.php (or simply define this array at the top of your main PHP file)
$menuLinks = [
    ['name' => 'Home', 'url' => 'index.php', 'target' => '_self', 'text' => 'Ir a Inicio'],
    ['name' => 'Vocabulario', 'url' => 'page/vocabulario.php', 'target' => '_self', 'text' => 'Acceder'],
    ['name' => 'Base de Git', 'url' => 'page/base-git.php', 'target' => '_self', 'text' => 'Acceder'],
    ['name' => 'Gimnasio', 'url' => 'page/gym.php', 'target' => '_self', 'text' => 'Acceder'],
    ['name' => 'Donaciones', 'url' => 'https://peaceful-ridge-28287-bf376195425c.herokuapp.com/', 'target' => '_blank', 'rel' => 'noopener noreferrer', 'text' => 'Acceder'],
    ['name' => 'Repositorios Git', 'url' => 'page/git-repos.php', 'target' => '_self', 'text' => 'Acceder'],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INICIO | Tu Sitio</title> <link rel="icon" href="public/assets/icon/logo1.svg" type="image/x-icon"> <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/styles1.css">

    <script src="public/js/main.js" defer></script>
</head>
<body>

    <header class="main-header">
        <div class="container">
            <h1 class="site-title">Hello, my name is Gabo</h1>
            <nav class="main-nav">
                </nav>
        </div>
    </header>

    <main class="main-content">
        <section id="main-menu" class="menu-section">
            <div class="container">
                <h2 class="section-title">main menu</h2>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menuLinks as $link): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($link['name']); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($link['url']); ?>"
                                       <?php if (isset($link['target'])): ?>target="<?php echo htmlspecialchars($link['target']); ?>"<?php endif; ?>
                                       <?php if (isset($link['rel'])): ?>rel="<?php echo htmlspecialchars($link['rel']); ?>"<?php endif; ?>>
                                        <?php echo htmlspecialchars($link['text']); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; 2025 MARCO 18 | Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>