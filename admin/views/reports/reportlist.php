<?php
require_once '../../islem/conn.php';
$title = "Raporlar";
ob_start();

$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Günlük Doluluk Raporu
$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE DATE(entrance) BETWEEN :start AND :end");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$toplamGiris = $stmt->fetchColumn();

$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE DATE(exit_time) BETWEEN :start AND :end");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$toplamCikis = $stmt->fetchColumn();

$stmt = $baglanti->prepare("SELECT SUM(fee) as toplamGelir FROM cars WHERE DATE(exit_time) BETWEEN :start AND :end");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$toplamGelir = $stmt->fetch(PDO::FETCH_ASSOC)['toplamGelir'] ?? 0;

$toplamAlan = 200;
$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE DATE(entrance) BETWEEN :start AND :end AND situation='Giriş'");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$doluAlan = $stmt->fetchColumn();
$ortalamaDoluluk = round(($doluAlan / $toplamAlan) * 100);

// Günlük Doluluk Trendi (saatlik)
$dolulukSaatlik = [];
for ($hour = 0; $hour < 24; $hour++) {
    $start = sprintf("%02d:00:00", $hour);
    $end = sprintf("%02d:59:59", $hour);
    $stmt = $baglanti->prepare("SELECT COUNT(*) as count FROM cars WHERE DATE(entrance) BETWEEN :startDate AND :endDate AND TIME(entrance) BETWEEN :start AND :end AND situation='Giriş'");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate, 'start' => $start, 'end' => $end]);
    $dolulukSaatlik[] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Aylık Gelir Raporu
$stmt = $baglanti->query("SELECT MONTH(exit_time) as ay, SUM(fee) as gelir FROM cars WHERE YEAR(exit_time)=YEAR(CURDATE()) GROUP BY MONTH(exit_time)");
$aylikGelirData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Araç Giriş/Çıkış Raporu
$stmt = $baglanti->prepare("SELECT plate, type, brand, entrance, exit_time, fee, situation FROM cars WHERE DATE(entrance) BETWEEN :start AND :end ORDER BY entrance DESC");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$aracRapor = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<main id="content">
    <h1 class="page-title">Raporlar ve İstatistikler</h1>
    <p class="subtitle">Otopark performansını detaylı raporlarla analiz edin.</p>

    <!-- Rapor Kontrol Paneli -->
    <div class="report-control-panel">
        <div class="report-option-group">
            <label for="report-type">Rapor Türü:</label>
            <select id="report-type" class="report-select">
                <option value="daily">Günlük Doluluk Raporu</option>
                <option value="monthly">Aylık Gelir Raporu</option>
                <option value="vehicle">Araç Giriş/Çıkış Raporu</option>
            </select>
        </div>
        <div class="report-option-group">
            <label for="date-range">Tarih Aralığı:</label>
            <input type="date" id="date-range" class="report-date-input" value="<?= $startDate ?>">
        </div>
        <button class="generate-report-btn">
            <i class="fas fa-chart-line"></i> Rapor Oluştur
        </button>
    </div>

    <!-- Günlük Doluluk Grafiği -->
    <div class="report-chart-section" id="daily-report-section">
        <h2>Günlük Doluluk Raporu</h2>
        <canvas id="dailyOccupancyChart"></canvas>
        <div class="summary-cards-grid">
            <div class="summary-card">
                <p>Ortalama Doluluk</p>
                <h3><?= $ortalamaDoluluk ?>%</h3>
            </div>
            <div class="summary-card">
                <p>Toplam Giriş</p>
                <h3><?= $toplamGiris ?></h3>
            </div>
            <div class="summary-card">
                <p>Toplam Çıkış</p>
                <h3><?= $toplamCikis ?></h3>
            </div>
            <div class="summary-card">
                <p>Toplam Gelir</p>
                <h3><?= number_format($toplamGelir, 2, ',', '.') ?> TL</h3>
            </div>
        </div>
    </div>

    <!-- Aylık Gelir Raporu -->
    <div class="report-chart-section" id="monthly-report-section" style="display:none;">
        <h2>Aylık Gelir Raporu</h2>
        <canvas id="monthlyIncomeChart"></canvas>
    </div>

    <!-- Araç Giriş/Çıkış Raporu -->
    <div class="report-chart-section" id="vehicle-report-section" style="display:none;">
        <h2>Araç Giriş/Çıkış Raporu</h2>
        <table>
            <thead>
                <tr>
                    <th>Plaka</th>
                    <th>Tür</th>
                    <th>Marka</th>
                    <th>Giriş</th>
                    <th>Çıkış</th>
                    <th>Ücret</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aracRapor as $arac): ?>
                    <tr>
                        <td><?= htmlspecialchars($arac['plate']) ?></td>
                        <td><?= htmlspecialchars($arac['type']) ?></td>
                        <td><?= htmlspecialchars($arac['brand']) ?></td>
                        <td><?= date("Y-m-d H:i", strtotime($arac['entrance'])) ?></td>
                        <td><?= !empty($arac['exit_time']) ? date("Y-m-d H:i", strtotime($arac['exit_time'])) : '-' ?></td>
                        <td><?= !empty($arac['fee']) ? htmlspecialchars($arac['fee']) . ' TL' : '-' ?></td>
                        <td><?= $arac['situation'] == 'Giriş' ? 'Otopark İçinde' : 'Dışarıda' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Günlük Doluluk Grafiği
    const ctxDaily = document.getElementById('dailyOccupancyChart').getContext('2d');
    const dailyOccupancyChart = new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: [<?php for ($h = 0; $h < 24; $h++) {
                echo "'$h:00',";
            } ?>],
            datasets: [{
                label: 'Araç Sayısı',
                data: [<?= implode(',', $dolulukSaatlik) ?>],
                borderColor: 'rgba(54,162,235,1)',
                backgroundColor: 'rgba(54,162,235,0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, stepSize: 1 } }, plugins: { legend: { display: true } } }
    });

    // Aylık Gelir Grafiği
    const ctxMonthly = document.getElementById('monthlyIncomeChart').getContext('2d');
    const monthlyIncomeChart = new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: [<?php foreach ($aylikGelirData as $row) {
                echo "'" . $row['ay'] . "',";
            } ?>],
            datasets: [{
                label: 'Gelir',
                data: [<?php foreach ($aylikGelirData as $row) {
                    echo $row['gelir'] . ',';
                } ?>],
                backgroundColor: 'rgba(255,99,132,0.6)',
                borderColor: 'rgba(255,99,132,1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Rapor Türüne göre göster/gizle
    document.getElementById('report-type').addEventListener('change', function () {
        const val = this.value;
        document.getElementById('daily-report-section').style.display = val == 'daily' ? 'block' : 'none';
        document.getElementById('monthly-report-section').style.display = val == 'monthly' ? 'block' : 'none';
        document.getElementById('vehicle-report-section').style.display = val == 'vehicle' ? 'block' : 'none';
    });
</script>

<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>