<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Yönetici Paneli</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo2.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/loginpage.css">
</head>

<body>

    <div class="login-container">

        <div class="login-card">
            <div class="card-header">
                <i class="fas fa-parking"></i>
                <h1>PARK<b class="highlight">HUB</b></h1>
                <p>Yönetici Paneline Giriş Yap</p>
            </div>

            <form method="POST" action="../../islem/islem.php" class="login-form">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Kullanıcı adınızı girin" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Şifre</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Şifrenizi girin" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">

                    </label>

                </div>

                <button name="admingiris" type="submit" class="login-btn">
                    Giriş Yap
                </button>
            </form>
        </div>

    </div>

</body>

</html>