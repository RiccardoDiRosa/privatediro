<?php
// Configurazione
$accessCode = '50ANNI'; // Codice di accesso per l'upload delle foto
$uploadDir = 'uploads/'; // Directory dove salvare le foto caricate

// Crea la directory se non esiste
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Inizializza la risposta
$response = array(
    'success' => false,
    'message' => ''
);

// Verifica se è una richiesta POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica il codice di accesso
    if (isset($_POST['accessCode']) && $_POST['accessCode'] === $accessCode) {
        // Verifica se sono stati caricati file
        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $uploaderName = isset($_POST['uploaderName']) ? $_POST['uploaderName'] : 'Anonimo';
            $totalFiles = count($_FILES['photos']['name']);
            $successCount = 0;
            
            // Processa ogni file
            for ($i = 0; $i < $totalFiles; $i++) {
                $fileName = $_FILES['photos']['name'][$i];
                $tmpName = $_FILES['photos']['tmp_name'][$i];
                $fileSize = $_FILES['photos']['size'][$i];
                $fileError = $_FILES['photos']['error'][$i];
                
                // Verifica errori di upload
                if ($fileError === 0) {
                    // Verifica estensione del file
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
                    
                    if (in_array($fileExt, $allowedExtensions)) {
                        // Verifica dimensione del file (max 5MB)
                        if ($fileSize <= 5000000) {
                            // Genera un nome file unico
                            $newFileName = $uploaderName . '_' . uniqid() . '.' . $fileExt;
                            $destination = $uploadDir . $newFileName;
                            
                            // Sposta il file nella directory di destinazione
                            if (move_uploaded_file($tmpName, $destination)) {
                                $successCount++;
                            }
                        } else {
                            $response['message'] = 'Alcuni file sono troppo grandi. Dimensione massima: 5MB.';
                        }
                    } else {
                        $response['message'] = 'Sono consentiti solo file JPG, JPEG, PNG e GIF.';
                    }
                } else {
                    $response['message'] = 'Si è verificato un errore durante l\'upload.';
                }
            }
            
            // Aggiorna la risposta in base al risultato
            if ($successCount > 0) {
                $response['success'] = true;
                $response['message'] = $successCount . ' foto ' . ($successCount === 1 ? 'è stata caricata' : 'sono state caricate') . ' con successo!';
            } else if (empty($response['message'])) {
                $response['message'] = 'Nessuna foto è stata caricata. Si prega di riprovare.';
            }
        } else {
            $response['message'] = 'Nessuna foto selezionata.';
        }
    } else {
        $response['message'] = 'Codice di accesso non valido.';
    }
}

// Restituisci la risposta in formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
