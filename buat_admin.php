
<?php
require_once "config/koneksi.php";

$nama = "Administrator";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

// Cek apakah username sudah ada
$cek = mysqli_prepare(
    $koneksi,
    "SELECT id_user FROM tbl_user WHERE username = ?"
);

mysqli_stmt_bind_param($cek, "s", $username);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
    echo "Akun admin sudah tersedia!";
} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tbl_user (nama, username, password, role)
         VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $nama,
        $username,
        $password,
        $role
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "Akun admin berhasil dibuat!";
        echo "<br>Username: admin";
        echo "<br>Password: admin123";
    } else {
        echo "Gagal membuat akun admin!";
    }

    mysqli_stmt_close($stmt);
}

mysqli_stmt_close($cek);
?>
