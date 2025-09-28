<?php
require_once '../../islem/conn.php';
?>


<?php
// Toplam kapasite
$toplamAlan = 200;

// Dolu alan (situation = 'Giriş')
$stmt = $baglanti->query("SELECT COUNT(*) FROM cars WHERE situation = 'Giriş'");
$doluAlan = $stmt->fetchColumn();

// Günlük gelir (son 24 saat çıkış yapanlar)
$stmt = $baglanti->prepare("SELECT SUM(fee) as toplamGelir FROM cars WHERE exit_time >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$gelirData = $stmt->fetch(PDO::FETCH_ASSOC);
$gunlukGelir = $gelirData['toplamGelir'] ?? 0;

// Doluluk oranı
$dolulukOrani = ($doluAlan / $toplamAlan) * 100;
?>


<?php
// Son 10 etkinlik (giriş veya çıkış)
$stmt = $baglanti->prepare("SELECT plate, situation, entrance, exit_time 
                            FROM cars 
                            ORDER BY GREATEST(COALESCE(exit_time, '0000-00-00 00:00:00'), entrance) DESC 
                            LIMIT 10");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// Saatlik doluluk sayısını alıyoruz (0-23 saat)
$dolulukSaatlik = [];

for ($hour = 0; $hour < 24; $hour++) {
    $start = sprintf("%02d:00:00", $hour);
    $end = sprintf("%02d:59:59", $hour);

    $stmt = $baglanti->prepare("SELECT COUNT(*) as count 
                                FROM cars 
                                WHERE TIME(entrance) BETWEEN :start AND :end
                                AND situation = 'Giriş'");
    $stmt->execute(['start' => $start, 'end' => $end]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $dolulukSaatlik[] = (int) $count;
}
?>



<main class="main-content">
    <h1 class="page-title">Ana Sayfa</h1>
    <p class="subtitle">Otoparkınızın anlık durumunu izleyin ve yönetin.</p>


    <div class="kpi-widgets-grid">
    </div>

    <div class="shortcut-panel">
        <h2 class="shortcut-title">Hızlı İşlemler</h2>
        <a href="../followup/addcar.php" class="shortcut-button">
            <i class="fas fa-plus-circle"></i>
            <span>Yeni Araç Girişi</span>
        </a>
    </div>

    <div class="dashboard-sections">
    </div>

    <div class="kpi-widgets-grid">
        <div class="widget large-widget">
            <div class="widget-icon"><i class="fas fa-percent"></i></div>
            <div class="widget-info">
                <h3>Doluluk Oranı</h3>
                <p class="data"><b><?= round($dolulukOrani) ?></b>%</p>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?= round($dolulukOrani) ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="widget">
            <div class="widget-icon"><i class="fas fa-car-on"></i></div>
            <div class="widget-info">
                <h3>Dolu Alan</h3>
                <p class="data"><b><?= $doluAlan ?></b></p>
                <p class="sub-data">toplam <?= $toplamAlan ?> alandan</p>
            </div>
        </div>

        <div class="widget">
            <div class="widget-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="widget-info">
                <h3>Günlük Gelir</h3>
                <p class="data"><b><?= number_format($gunlukGelir, 2, ',', '.') ?> TL</b></p>
                <p class="sub-data">son 24 saat</p>
            </div>
        </div>
    </div>
    <div class="dashboard-sections">
        <div class="recent-activity-section">
            <h2><i class="fas fa-history"></i> Son Etkinlikler</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Plaka</th>
                            <th>Giriş / Çıkış</th>
                            <th>Zaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td style="font-weight: 800;"><?= htmlspecialchars($event['plate']) ?></td>
                                <td>
                                    <?php if ($event['situation'] == 'Giriş'): ?>
                                        <span class="status-entry">Giriş</span>
                                    <?php else: ?>
                                        <span class="status-exit">Çıkış</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($event['situation'] == 'Giriş') {
                                        echo date("H:i", strtotime($event['entrance']));
                                    } else {
                                        echo date("H:i", strtotime($event['exit_time']));
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="chart-section">
            <h2><i class="fas fa-chart-line"></i> Günlük Doluluk Trendi</h2>
            <canvas id="dailyOccupancyChart"></canvas>
        </div>
    </div>
</main>