<?php
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkHub - Park Ücreti Sorgulama</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/logo2.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="user-page-container">
        <div class="background-overlay"></div>
        <div class="user-card">
            <div class="user-logo">
                <i class="fas fa-car-space"></i>
                <h1>PARK<b class="highlight">HUB</b></h1>
            </div>
            <h2 class="card-title">Plakanızı Girin</h2>
            <p class="card-subtitle">Otoparktaki durumunuzu ve ödeme bilgilerinizi görüntüleyin.</p>
            <form action="views/detail/detail.php" method="POST" class="plate-form">
                <div class="form-group">
                    <input type="text" id="plate" name="plate" placeholder="Örn: 34 ABC 123" required>
                </div>

                <button type="submit" class="submit-button">
                    <i class="fas fa-search"></i> Sorgula
                </button>
            </form>

        </div>
    </div>

    <script>
        // Plaka alanına girilen metni otomatik olarak büyük harfe çevirir
        const plateInput = document.getElementById('plate');
        plateInput.addEventListener('input', function (event) {
            event.target.value = event.target.value.toUpperCase();
        });
    </script>
    <script>
        document.getElementById('plate').addEventListener('input', function (e) {
            let val = e.target.value.toUpperCase();   // Harfleri büyüt
            val = val.replace(/\s+/g, '');            // Tüm boşlukları kaldır (önce temizle)

            const match = val.match(/^(\d{1,2})([A-Z]{1,3})(\d{1,4})$/);

            if (match) {
                // Format: 34 ABC 1234
                val = `${match[1]} ${match[2]} ${match[3]}`;
            }

            e.target.value = val;  // Düzenlenmiş halini input'a yaz
        });
    </script>

</body>

</html>