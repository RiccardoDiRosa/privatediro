<?php
// Configurazione
$accessCode = '50ANNI'; // Codice di accesso per visualizzare la galleria
$uploadDir = 'uploads/'; // Directory dove sono salvate le foto
$isAuthenticated = false;
$errorMessage = '';

// Verifica se è stato inviato il codice di accesso
if (isset($_POST['accessCode'])) {
    if ($_POST['accessCode'] === $accessCode) {
        $isAuthenticated = true;
        // Imposta un cookie per mantenere l'accesso (valido per 1 ora)
        setcookie('gallery_access', md5($accessCode), time() + 3600, '/');
    } else {
        $errorMessage = 'Codice di accesso non valido. Riprova.';
    }
} else if (isset($_COOKIE['gallery_access']) && $_COOKIE['gallery_access'] === md5($accessCode)) {
    // Verifica se il cookie di accesso è valido
    $isAuthenticated = true;
}

// Funzione per ottenere tutte le foto dalla directory
function getPhotos($dir) {
    $photos = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $photos[] = [
                        'path' => $dir . $file,
                        'name' => $file,
                        'date' => filemtime($dir . $file)
                    ];
                }
            }
        }
        // Ordina le foto per data (più recenti prima)
        usort($photos, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
    return $photos;
}

// Ottieni le foto solo se l'utente è autenticato
$photos = $isAuthenticated ? getPhotos($uploadDir) : [];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galleria Foto - 50° Anniversario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="anniversario.css">
    <style>
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            grid-gap: 15px;
            margin-top: 20px;
        }
        
        .photo-item {
            height: 200px;
            overflow: hidden;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .photo-item:hover img {
            transform: scale(1.1);
        }
        
        .photo-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px;
            font-size: 0.8rem;
            transform: translateY(100%);
            transition: transform 0.3s;
        }
        
        .photo-item:hover .photo-info {
            transform: translateY(0);
        }
        
        .no-photos {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="anniversario.html">50° Anniversario</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="anniversario.html#info">Informazioni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anniversario.html#location">Luogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anniversario.html#gallery">Galleria</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anniversario.html#upload">Carica Foto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anniversario.html#gift">Regalo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="galleria.php">Foto Evento</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section" style="padding: 120px 0 80px;">
        <div class="container">
            <h1 class="display-4 mb-4 animate-fade-in">Galleria Foto Evento</h1>
            <p class="lead mb-0 animate-fade-in delay-1">Rivivi i momenti più belli della serata</p>
        </div>
    </div>

    <!-- Contenuto Galleria -->
    <section class="py-5">
        <div class="container">
            <?php if (!$isAuthenticated): ?>
                <!-- Form di accesso -->
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="card p-4">
                            <h2 class="text-center mb-4">Accesso Galleria</h2>
                            <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                            <?php endif; ?>
                            <p class="text-center mb-4">Inserisci il codice di accesso per visualizzare le foto dell'evento</p>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="accessCode" class="form-label">Codice di Accesso</label>
                                    <input type="password" class="form-control" id="accessCode" name="accessCode" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Accedi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Galleria foto -->
                <h2 class="section-title">Le Foto dell'Evento</h2>
                
                <?php if (empty($photos)): ?>
                    <div class="no-photos">
                        <i class="fas fa-camera fa-3x mb-3 text-muted"></i>
                        <h3>Nessuna foto disponibile</h3>
                        <p>Le foto caricate dagli invitati appariranno qui. Sii il primo a condividere i tuoi ricordi!</p>
                        <a href="anniversario.html#upload" class="btn btn-primary mt-3">Carica Foto</a>
                    </div>
                <?php else: ?>
                    <div class="photo-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item" data-bs-toggle="modal" data-bs-target="#photoModal" data-img="<?php echo $photo['path']; ?>">
                                <img src="<?php echo $photo['path']; ?>" alt="Foto evento">
                                <div class="photo-info">
                                    <?php 
                                    $photoName = explode('_', $photo['name'])[0]; // Estrae il nome dell'uploader
                                    $photoDate = date('d/m/Y H:i', $photo['date']);
                                    echo $photoName . ' - ' . $photoDate;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal per visualizzare le foto -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-photo-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modal-photo" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p>© 2023 - 50° Anniversario</p>
            <p>Per informazioni: <a href="mailto:info@50anniversario.it" class="text-white">info@50anniversario.it</a></p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Modal per le foto
        document.querySelectorAll('.photo-item').forEach(item => {
            item.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-img');
                const photoInfo = this.querySelector('.photo-info').textContent;
                document.getElementById('modal-photo').src = imgSrc;
                document.getElementById('modal-photo-title').textContent = photoInfo;
            });
        });
    </script>
</body>
</html>
