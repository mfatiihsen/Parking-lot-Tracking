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

<style>
    /* Genel stiller */
    /* Hata mesajı için stiller */
    .error-message {
        background-color: rgba(231, 76, 60, 0.1);
        border-left: 4px solid #e74c3c;
        color: #e74c3c;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    .error-message i {
        margin-right: 10px;
        font-size: 10px;
    }

    .error-input {
        border-color: red 1px solid;
    }

    .error-input:focus {
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2) !important;
    }

    /* Başarılı mesajı için stiller */
    .success-message {
        background-color: rgba(46, 204, 113, 0.1);
        border-left: 4px solid #2ecc71;
        color: #2ecc71;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        animation: fadeIn 0.5s ease-out;
    }

    .success-message i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    /* Responsive tasarım */
    @media (max-width: 480px) {
        .login-form {
            padding: 20px;
        }

        .card-header {
            padding: 20px 15px;
        }

        .card-header h1 {
            font-size: 1.7rem;
        }
    }
</style>

<body>

    <div class="login-container">

        <div class="login-card">
            <div class="card-header">
                <i class="fas fa-parking"></i>
                <h1>PARK<b class="highlight">HUB</b></h1>
                <p>Yönetici Paneline Giriş Yap</p>
            </div>

            <!-- Hata mesajı burada gösterilecek -->
            <div id="errorMessage" class="error-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText">Kullanıcı adı veya şifre hatalı!</span>
            </div>


            <form method="POST" action="../../islem/islem.php" class="login-form">
                <div class="form-group">
                    <label for="username"></label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Kullanıcı adınızı girin" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"></label>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        // URL parametrelerini kontrol et
        const urlParams = new URLSearchParams(window.location.search);
        const girisDurumu = urlParams.get('giris');

        if (girisDurumu === 'basarisiz') {
            showError('Kullanıcı adı veya şifre hatalı! Lütfen tekrar deneyin.');
        } else if (girisDurumu === 'basarili') {
            showSuccess('Giriş başarılı! Yönlendiriliyorsunuz...');
            // 2 saniye sonra ana sayfaya yönlendir
            setTimeout(function () {
                window.location.href = '../views/home/index.php';
            }, 2000);
        }

        // Form gönderimini dinle
        loginForm.addEventListener('submit', function (e) {
            // Ön tarafta basit doğrulama
            if (!usernameInput.value.trim() || !passwordInput.value.trim()) {
                e.preventDefault();
                showError('Lütfen tüm alanları doldurun!');
                return;
            }

            // Form gönderiliyor, butonu devre dışı bırak
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş Yapılıyor...';
        });

        // Hata mesajını gösteren fonksiyon
        function showError(message) {
            document.getElementById('errorText').textContent = message;
            errorMessage.style.display = 'flex';
            successMessage.style.display = 'none';

            // Input'lara hata sınıfını ekle
            usernameInput.classList.add('error-input');
            passwordInput.classList.add('error-input');

            // 5 saniye sonra hata mesajını gizle
            setTimeout(function () {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        // Başarı mesajını gösteren fonksiyon
        function showSuccess(message) {
            document.getElementById('successText').textContent = message;
            successMessage.style.display = 'flex';
            errorMessage.style.display = 'none';

            // Input'lardan hata sınıfını kaldır
            usernameInput.classList.remove('error-input');
            passwordInput.classList.remove('error-input');
        }

        // Input değişikliklerinde hata sınıfını kaldır
        usernameInput.addEventListener('input', function () {
            if (this.classList.contains('error-input')) {
                this.classList.remove('error-input');
                errorMessage.style.display = 'none';
            }
        });

        passwordInput.addEventListener('input', function () {
            if (this.classList.contains('error-input')) {
                this.classList.remove('error-input');
                errorMessage.style.display = 'none';
            }
        });
    });
</script>



</html>