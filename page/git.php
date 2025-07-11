<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Código</title>
    <link rel="stylesheet" href="../css/git.css">
    <!-- Agregamos FontAwesome para los íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
        
    <img src="../imagenes/codigos.jpeg">

    <div class="container">
        <br>
        <img src="../imagenes/codigos.jpeg">
        <h1>Mi Repositorio de Código</h1>


        <!-- Título "Códigos" -->
        <h2 class="section-title">CODIGOS</h2>

        <!-- Sección Git Bash -->
        <div class="section">
            <h3>Git Bash para Guardar Cambios en mi Carpeta local</h3>
            <p>En esta sección encontrarás los comandos básicos para guardar cambios en un repositorio utilizando Git Bash.</p>

            <!-- Paso 1: Git Push -->
            <div class="code-block">
                <h4>1. Guardar cambion proyecto en Github</h4>
                <pre><code id="git-push">
git status
git add . 
git commit -m "Descripción de los cambios realizados" 
git push origin main
                </code></pre>
                <button class="copyButton" data-code="git-push"><i class="fas fa-copy"></i> Copiar</button>
                <p id="status-git-push" class="status"></p>
            </div>

            <!-- Paso 2: Git Add -->
            <div class="code-block">
                <h4>2. Paso (Comprobar)</h4>
                <pre><code id="git-add">
git add .
                </code></pre>
                <button class="copyButton" data-code="git-add"><i class="fas fa-copy"></i> Copiar</button>
                <p id="status-git-add" class="status"></p>
            </div>
            <div class="code-block">
                <h4>1. git commit "hello worth"</h4>
                <pre><code id="git-1">
git commit -m "Descripción de los cambios realizados"

                </code></pre>
                <button class="copyButton" data-code="git-1"><i class="fas fa-copy"></i> Copiar</button>
                <p id="status-git-1" class="status"></p>
            </div>
            <div class="code-block">
                <h4>1. git push</h4>
                <pre><code id="git-2">
git push origin master
 
                </code></pre>
                <button class="copyButton" data-code="git-2"><i class="fas fa-copy"></i> Copiar</button>
                <p id="status-git-2" class="status"></p>
            </div>
        </div>

        <!-- Sección Arduino -->
        <div class="section">
            <h3>Subir proyecto</h3>
            <p>Esta sección contiene los comandos utilizados para subir cambios relacionados con Github.</p>

            <!-- Paso 1: Arduino Push -->
            <div class="code-block">
                <h4>1. Paso basico (subir)</h4>
                <pre><code id="arduino-push">
                    git init 
                    git add README.md 
                    git commit -m "primer commit" 
                    git branch -M main 
                    git remote add origin "TU DIRECCION DEL PROYECTO"
                     git push -u origin main
                </code></pre>
                <button class="copyButton" data-code="arduino-push"><i class="fas fa-copy"></i> Copiar Línea "arduino push"</button>
                <p id="status-arduino-push" class="status"></p>
            </div>

            <!-- Paso 2: Git Add Arduino -->
            <div class="code-block">
                <h4>2. Paso (Comprobar)</h4>
                <pre><code id="git-add-arduino">
git add arduino
                </code></pre>
                <button class="copyButton" data-code="git-add-arduino"><i class="fas fa-copy"></i> Copiar Línea "git add arduino"</button>
                <p id="status-git-add-arduino" class="status"></p>
            </div>
        </div>
    </div>

    <script src="../js/git.js"></script>
</body>
</html>
