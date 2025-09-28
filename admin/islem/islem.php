<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php


require_once 'conn.php';



#admin giriş işlemi


session_start();

if (isset($_POST['admingiris'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $strongpassword = md5($password);


    $askadmin = $baglanti->prepare("SELECT * from admin where username=:username and password=:password");
    $askadmin->execute(array(
        'username' => $username,
        'password' => $password
    ));


    $var = $askadmin->rowCount();
    if ($var > 0) {
        $admin = $askadmin->fetch(PDO::FETCH_ASSOC);
        $_SESSION['name'] = $admin['name'];
        $_SESSION['id'] = $admin['id'];
        $_SESSION['authority'] = $admin['authority'];
        echo $admin['name'];

        $_SESSION['girisbelgesi'] = $username;
        $_SESSION['girisbelgesi'] = true;

        header('Location:../views/home/index.php?giris=basarili');
    } else {
        header('Location:../views/loginpage/login.php?giris=basarisiz');
    }
}



#admin ekleme işlemi


if (isset($_POST['adminadd'])) {
    $name = htmlspecialchars($_POST['name']);
    $surname = htmlspecialchars($_POST['surname']);
    $username = htmlspecialchars($_POST['username']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $passwordconfirm = htmlspecialchars($_POST['password-confirm']);
    $authority = htmlspecialchars($_POST['authority']);


    $askadmin = $baglanti->prepare("SELECT * from admin where mail=:mail");
    $askadmin->execute(array(
        'mail' => $email
    ));

    $var = $askadmin->rowCount();
    if ($var > 0) {
        header('Location:../views/admins/adminadd.php?durum=personelvar');
    } else {
        $adminadd = $baglanti->prepare("INSERT INTO admin (name,surname,username,phone,mail,password,authority) VALUES (:name,:surname,:username,:phone,:mail,:password,:authority)");
        $insert = $adminadd->execute(array(
            'name' => $name,
            'surname' => $surname,
            'username' => $username,
            'phone' => $phone,
            'mail' => $email,
            'password' => $password,
            'authority' => $authority
        ));
        if ($insert) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: 'Başarılı!',
                    text: 'Giriş başarıyla eklendi.',
                    icon: 'success',
                    button: 'Tamam'
                }).then(function() {
                    window.location = '../views/admins/adminlist.php';
                });
            });
            </script>";
        } else {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: 'Hata!',
                    text: 'Giriş Eklenirken bir hata oluştu.',
                    icon: 'error',
                    button: 'Tamam'
                }).then(function() {
                    window.location = adminadd.php';
                });
            });
            </script>";
        }
    }
}


#admin oturum kapatma işlemleri

// oturum kapatma işlemi
if (isset($_POST['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../views/loginpage/login.php'); // Giriş sayfasına yönlendirme
    exit;
}


#admin sil

if (isset($_GET['adminsil'])) {
    $adminsil = $baglanti->prepare("DELETE from admin where id=:id");
    $adminsil->execute(array(
        'id' => $_GET['id']
    ));

    if ($adminsil) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            swal({
                title: 'Başarılı!',
                text: 'Silme işlemi başarılı.',
                icon: 'success',
                button: 'Tamam'
            }).then(function() {
                window.location = '../views/admins/adminlist.php';
            });
        });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: 'Hata!',
                    text: 'Silme işleminde bir hata oluştu.',
                    icon: 'error',
                    button: 'Tamam'
                }).then(function() {
                    window.location = '../views/admins/adminlist.php';
                });
            });
            </script>";
    }
}



#yeni araç kayıt ekle

if (isset($_POST["caradd"])) {
    date_default_timezone_set('Europe/Istanbul');

    $plate = strtoupper($_POST['plate']);
    $brand = $_POST['brand'];
    $status = $_POST['status'];
    $type = $_POST['type'];

    // OTOMATİK ZAMAN - FORM ZAMANINI TAMAMEN KALDIR
    $entrance = date('Y-m-d H:i:s');

    

    $sql = "INSERT INTO cars (plate, brand, entrance, type, situation) 
            VALUES (:plate, :brand, :entrance, :type, :situation)";
    $stmt = $baglanti->prepare($sql);

    if (
        $stmt->execute([
            ':plate' => $plate,
            ':brand' => $brand,
            ':entrance' => $entrance,
            ':type' => $type,
            ':situation' => $status
        ])
    ) {
        header("Location:../views/followup/follow-up.php?durum=ok");
        exit;
    } else {
        echo "Hata: " . implode(" ", $stmt->errorInfo());
    }
}

#otopark çıkış işlemi
if (isset($_POST['car_id'])) {
    date_default_timezone_set('Europe/Istanbul');
    $carId = $_POST['car_id'];

    // Aracın giriş zamanını al
    $stmt = $baglanti->prepare("SELECT entrance FROM cars WHERE id = :id");
    $stmt->execute([':id' => $carId]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($car) {
        // Zaman dilimini belirterek DateTime nesneleri oluştur
        $entrance = new DateTime($car['entrance'], new DateTimeZone('Europe/Istanbul'));
        $exit = new DateTime('now', new DateTimeZone('Europe/Istanbul')); // şu anki zaman

        // Debug için zamanları kontrol et
        error_log("Giriş Zamanı: " . $car['entrance']);
        error_log("Giriş DateTime: " . $entrance->format('Y-m-d H:i:s'));
        error_log("Çıkış DateTime: " . $exit->format('Y-m-d H:i:s'));

        // Toplam saniye farkını hesapla (daha güvenli yöntem)
        $entrance_timestamp = $entrance->getTimestamp();
        $exit_timestamp = $exit->getTimestamp();
        $diff_seconds = $exit_timestamp - $entrance_timestamp;

        // Negatif süre kontrolü
        if ($diff_seconds < 0) {
            $diff_seconds = 0;
        }

        // Toplam dakikayı hesapla (en az 1 dakika)
        $minutes = max(1, ceil($diff_seconds / 60));

        // Ücreti hesapla (dakika başına 2 TL)
        $fee = $minutes * 2;

        // Debug için hesaplanan değerleri logla
        error_log("Fark (saniye): " . $diff_seconds);
        error_log("Hesaplanan Dakika: " . $minutes);
        error_log("Hesaplanan Ücret: " . $fee);

        // Çıkış zamanı ve ücreti güncelle
        $update = $baglanti->prepare("UPDATE cars 
                                      SET exit_time = :exit_time, fee = :fee, situation = 'Çıkış' 
                                      WHERE id = :id");
        $success = $update->execute([
            ':exit_time' => $exit->format('Y-m-d H:i:s'),
            ':fee' => $fee,
            ':id' => $carId
        ]);

        if ($success) {
            header("Location: ../views/followup/follow-up.php?durum=exit_ok&fee=$fee&minutes=$minutes");
            exit;
        } else {
            echo "Hata: Çıkış kaydedilemedi.";
            error_log("SQL Hatası: " . implode(", ", $update->errorInfo()));
        }
    } else {
        echo "Araç bulunamadı!";
    }
}