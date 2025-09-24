<?php
require_once '../../data/db.php'; // PDO bağlantısı
date_default_timezone_set('Europe/Istanbul');

// Formdan gelen plaka
$plaka = strtoupper($_POST['plate'] ?? '');

// Plaka sorgulama
$stmt = $baglanti->prepare("SELECT * FROM cars WHERE plate = ?");
$stmt->execute([$plaka]);
$arac_bilgisi = $stmt->fetch(PDO::FETCH_ASSOC);

// Ücret ve geçen dakika hesaplama
$ucret = 0;
$dakika = 0;
$gecen_saniye = 0;
$dakika_ucreti = 2.00; // Dakika başına ücret (TL)

if ($arac_bilgisi && $arac_bilgisi['situation'] == 'Giriş') {
    // Veritabanındaki giriş zamanı
    $giris_timestamp = strtotime($arac_bilgisi['entrance']);
    $simdi_timestamp = time();

    // Geçen saniye farkı
    $gecen_saniye = $simdi_timestamp - $giris_timestamp;

    // Negatif değer kontrolü
    if ($gecen_saniye < 0) {
        $gecen_saniye = 0;
    }

    // Toplam dakika - en az 1 dakika
    $dakika = max(1, ceil($gecen_saniye / 60));

    // Ücret hesaplama
    $ucret = $dakika * $dakika_ucreti;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkHub - Plaka Sorgulama Sonucu</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="user-page-container">
        <div class="background-overlay"></div>
        <div class="user-card result-card">
            <div class="user-logo">
                <i class="fas fa-car-space"></i>
                <h1>PARK<b class="highlight">HUB</b></h1>
            </div>
            <h2 class="card-title">Plaka Sorgulama Sonucu</h2>
            <p class="card-subtitle">Girilen plakanın otoparktaki son durumu.</p>

            <?php if ($arac_bilgisi): ?>
                <div class="result-details">
                    <div class="detail-item"><span>Plaka:</span>
                        <strong><?= htmlspecialchars($arac_bilgisi['plate']) ?></strong>
                    </div>
                    <div class="detail-item"><span>Marka/Model:</span>
                        <strong><?= htmlspecialchars($arac_bilgisi['brand']) ?></strong>
                    </div>

                    <?php if ($arac_bilgisi['situation'] == 'Giriş'): ?>
                        <div class="detail-item"><span>Giriş Zamanı:</span>
                            <strong><?= date('d M Y, H:i:s', strtotime($arac_bilgisi['entrance'])) ?></strong>
                        </div>
                        <div class="detail-item"><span>Sorgulama Zamanı:</span>
                            <strong><?= date('d M Y, H:i:s') ?></strong>
                        </div>
                        <div class="detail-item"><span>Geçen Süre:</span>
                            <strong>
                                <?php
                                // Detaylı süre hesaplama
                                $saat = floor($gecen_saniye / 3600);
                                $dakika_kalan = floor(($gecen_saniye % 3600) / 60);
                                $saniye_kalan = $gecen_saniye % 60;
                                echo $saat . " saat " . $dakika_kalan . " dakika " . $saniye_kalan . " saniye";
                                ?>
                            </strong>
                        </div>
                        <div class="detail-item"><span>Toplam Dakika:</span>
                            <strong><?= $dakika ?> dakika</strong>
                        </div>
                        <div class="detail-item"><span>Dakika Ücreti:</span>
                            <strong><?= number_format($dakika_ucreti, 2) ?> TL</strong>
                        </div>
                        <div class="detail-item total-amount"><span>Ödenecek Ücret:</span>
                            <strong style="color: #e74c3c; font-size: 1.2em;"><?= number_format($ucret, 2) ?> TL</strong>
                        </div>

                    <?php else: ?>
                        <div class="detail-item"><span>Giriş Zamanı:</span>
                            <strong><?= date('d M Y, H:i', strtotime($arac_bilgisi['entrance'])) ?></strong>
                        </div>
                        <div class="detail-item"><span>Çıkış Zamanı:</span>
                            <strong><?= date('d M Y, H:i', strtotime($arac_bilgisi['exit_time'])) ?></strong>
                        </div>
                        <div class="detail-item"><span>Toplam Süre:</span>
                            <strong>
                                <?php
                                $giris_ts = strtotime($arac_bilgisi['entrance']);
                                $cikis_ts = strtotime($arac_bilgisi['exit_time']);
                                $toplam_saniye = $cikis_ts - $giris_ts;
                                $saat = floor($toplam_saniye / 3600);
                                $dakika = floor(($toplam_saniye % 3600) / 60);
                                echo $saat . " saat " . $dakika . " dakika";
                                ?>
                            </strong>
                        </div>
                        <div class="detail-item"><span>Toplam Ödeme:</span>
                            <strong><?= number_format(floatval($arac_bilgisi['fee']), 2) ?> TL</strong>
                        </div>
                    <?php endif; ?>

                    <div class="detail-item"><span>Durum:</span>
                        <strong class="status-<?= ($arac_bilgisi['situation'] == 'Giriş' ? 'parked' : 'exited') ?>">
                            <?= htmlspecialchars($arac_bilgisi['situation']) ?>
                        </strong>
                    </div>
                </div>

                <!-- Geri Dön Butonu -->
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="window.location.href='../../index.php'" class="back-button">
                        <i class="fas fa-arrow-left"></i> Yeni Sorgulama Yap
                    </button>
                </div>

            <?php else: ?>
                <div class="not-found-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Aradığınız plaka otopark kayıtlarında bulunamadı.</p>
                    <button onclick="window.location.href='../../index.php'" class="back-button">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

<?php
$stmt = null;
$baglanti = null;
?>