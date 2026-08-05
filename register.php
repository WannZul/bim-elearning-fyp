<?php
include 'includes/db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Hash password untuk keselamatan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Semak jika email sudah wujud
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $error = "Email sudah didaftarkan! Sila gunakan email lain.";
    } else {
        // Insert ke database
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
        if (mysqli_query($conn, $sql)) {
            $success = "Pendaftaran berjaya! Sila <a href='login.php'>Log Masuk</a>.";
        } else {
            $error = "Ralat: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akaun - BIM E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Daftar Akaun BIM</h3>
                        
                        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Penuh</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kata Laluan</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Daftar</button>
                        </form>
                        <p class="text-center mt-3">Sudah ada akaun? <a href="login.php">Log Masuk di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>