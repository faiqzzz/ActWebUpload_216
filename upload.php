<?php
$target_dir = "uploads/";
$uploadOk = 1;
$message = ""; // Variabel untuk menampung pesan status unggah

// BUNGKUS KODE ASLI AGAR HANYA BERJALAN JIKA ADA FILE YANG DIUNGGAH
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES["fileToUpload"])) {

    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Periksa apakah berkas sebenarnya adalah gambar atau bukan
    if(isset($_POST["submit"])) {
        // Cek apakah ukuran file 0 (misal tidak jadi upload atau error sistem)
        if($_FILES["fileToUpload"]["tmp_name"] != "") {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $message .= "<div class='alert success'>Berkas adalah gambar - " . $check["mime"] . ".</div>";
                $uploadOk = 1;
            } else {
                $message .= "<div class='alert danger'>Berkas bukan gambar asli.</div>";
                $uploadOk = 0;
            }
        }
    }

    // Periksa apakah berkas sudah ada
    if (file_exists($target_file)) {
        $message .= "<div class='alert danger'>Maaf, berkas sudah ada.</div>";
        $uploadOk = 0;
    }

    // Periksa ukuran berkas (dalam byte, 500000 byte = 500KB)
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $message .= "<div class='alert danger'>Maaf, berkas Anda terlalu besar (Maksimal 500KB).</div>";
        $uploadOk = 0;
    }

    // Hanya izinkan format berkas tertentu
    if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" ) {
        $message .= "<div class='alert danger'>Maaf, hanya berkas JPG, JPEG, PNG & GIF yang diperbolehkan.</div>";
        $uploadOk = 0;
    }

    // Periksa apakah $uploadOk bernilai 0 karena kesalahan
    if ($uploadOk == 0) {
        $message .= "<div class='alert danger'>Maaf, berkas Anda tidak dapat diunggah.</div>";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message .= "<div class='alert success'>Berkas <strong>". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). "</strong> telah diunggah.</div>";
        } else {
            $message .= "<div class='alert danger'>Maaf, terjadi kesalahan saat mengunggah berkas Anda.</div>";
        }
    }
} 

// ==========================================
// FITUR PROSES UNDUH FILE (Wajib di atas sebelum output HTML)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['file'])) {
    $file_to_download = $target_dir . basename($_GET['file']);
    
    if (file_exists($file_to_download) && is_file($file_to_download)) {
        if (ob_get_level()) { ob_end_clean(); }
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_to_download) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_to_download));
        
        readfile($file_to_download);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola File</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 700px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        h3 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 10px;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .success {
            background-color: #def7ec;
            color: #03543f;
            border: 1px solid #bcf0da;
        }
        .danger {
            background-color: #fde8e8;
            color: #9b1c1c;
            border: 1px solid #fbd5d5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 15px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .btn {
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }
        .btn-download {
            background-color: #3b82f6;
            color: white;
            margin-right: 5px;
        }
        .btn-download:hover { background-color: #2563eb; }
        
        .btn-delete {
            background-color: #ef4444;
            color: white;
        }
        .btn-delete:hover { background-color: #dc2626; }
        
        .btn-back {
            margin-top: 20px;
            color: #4b5563;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-back:hover { color: #1f2937; text-decoration: underline; }
        
        .empty-text {
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            padding: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    
    <?php if (!empty($message)) echo $message; ?>

    <h3>Daftar Berkas Terunggah</h3>

    <?php
    // FITUR PROSES HAPUS FILE (Dijalankan di dalam container agar alert-nya rapi)
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['file'])) {
        $file_to_delete = $target_dir . basename($_GET['file']);
        
        if (file_exists($file_to_delete) && is_file($file_to_delete)) {
            if (unlink($file_to_delete)) {
                echo "<div class='alert success'>Berkas '" . htmlspecialchars($_GET['file']) . "' berhasil dihapus.</div>";
            } else {
                echo "<div class='alert danger'>Gagal menghapus berkas.</div>";
            }
        } else {
            echo "<div class='alert danger'>Berkas tidak ditemukan atau sudah dihapus.</div>";
        }
    }

    // MENAMPILKAN DAFTAR FILE DAN TOMBOL AKSI DENGAN DESIGN BARU
    if (is_dir($target_dir)) {
        $files = scandir($target_dir);
        
        echo "<table>";
        echo "<thead><tr><th>Nama Berkas</th><th style='width: 150px;'>Aksi</th></tr></thead>";
        echo "<tbody>";
        
        $has_files = false;
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $has_files = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($file) . "</td>";
                echo "<td>";
                echo "<a class='btn btn-download' href='upload.php?action=download&file=" . urlencode($file) . "'>Unduh</a>";
                echo "<a class='btn btn-delete' href='upload.php?action=delete&file=" . urlencode($file) . "' onclick='return confirm(\"Apakah Anda yakin ingin menghapus file ini?\")'>Hapus</a>";
                echo "</td>";
                echo "</tr>";
            }
        }
        
        if (!$has_files) {
            echo "<tr><td colspan='2' class='empty-text'>Belum ada berkas yang diunggah.</td></tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    }
    ?>

    <a href="index.html" class="btn-back">← Kembali ke Halaman Unggah</a>
</div>

</body>
</html>