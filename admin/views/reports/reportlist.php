<?php
require_once '../../islem/conn.php';

// Filtreleme parametrelerini al
$reportType = $_GET['report_type'] ?? 'daily';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$vehicleType = $_GET['vehicle_type'] ?? 'all';

// Rapor verilerini çek
$sql = "SELECT 
            DATE(entrance) as tarih,
            COUNT(*) as toplam_arac,
            SUM(CASE WHEN situation = 'Giriş' THEN 1 ELSE 0 END) as icerdeki_arac,
            SUM(CASE WHEN situation = 'Çıkış' THEN 1 ELSE 0 END) as cikan_arac,
            SUM(fee) as toplam_ucret
        FROM cars 
        WHERE 1=1";

$params = [];

// Tarih filtresi
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND DATE(entrance) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

// Araç tipi filtresi
if ($vehicleType !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $vehicleType;
}

// Gruplama
if ($reportType === 'daily') {
    $sql .= " GROUP BY DATE(entrance) ORDER BY tarih DESC";
} elseif ($reportType === 'weekly') {
    $sql .= " GROUP BY YEAR(entrance), WEEK(entrance) ORDER BY tarih DESC";
} elseif ($reportType === 'monthly') {
    $sql .= " GROUP BY YEAR(entrance), MONTH(entrance) ORDER BY tarih DESC";
}

$stmt = $baglanti->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Toplam özet verileri
$summarySql = "SELECT 
                COUNT(*) as toplam_arac,
                SUM(CASE WHEN situation = 'Giriş' THEN 1 ELSE 0 END) as icerdeki_arac,
                SUM(fee) as toplam_ucret
              FROM cars 
              WHERE 1=1";

$summaryParams = [];
if (!empty($startDate) && !empty($endDate)) {
    $summarySql .= " AND DATE(entrance) BETWEEN ? AND ?";
    $summaryParams[] = $startDate;
    $summaryParams[] = $endDate;
}

if ($vehicleType !== 'all') {
    $summarySql .= " AND type = ?";
    $summaryParams[] = $vehicleType;
}

$summaryStmt = $baglanti->prepare($summarySql);
$summaryStmt->execute($summaryParams);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

// Araç tiplerini al
$vehicleTypesStmt = $baglanti->query("SELECT DISTINCT type FROM cars WHERE type IS NOT NULL AND type != ''");
$vehicleTypes = $vehicleTypesStmt->fetchAll(PDO::FETCH_COLUMN);

$title = "Raporlar";
ob_start();
?>

<!-- CSS'i ekleyelim -->
<style>
    /* Raporlar Sayfası Filtreleme Stilleri */
    .rp-panel {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .rp-title {
        color: #f0f2f5;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .rp-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .rp-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: flex-end;
    }

    .rp-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }

    .rp-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #b0c4de;
        font-size: 0.9rem;
    }

    .rp-select,
    .rp-input {
        padding: 0.8rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #f0f2f5;
        font-family: "Poppins", sans-serif;
        font-size: 0.9rem;
    }

    .rp-select:focus,
    .rp-input:focus {
        outline: none;
        border-color: #1abc9c;
    }

    .rp-buttons {
        flex-direction: row !important;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .rp-button {
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .rp-button-apply {
        background: #1abc9c;
        color: #0b1a2e;
    }

    .rp-button-apply:hover {
        background: #16a085;
        transform: translateY(-2px);
    }

    .rp-button-clear {
        background: rgba(255, 255, 255, 0.1);
        color: #f0f2f5;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .rp-button-clear:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .rp-results {
        background: rgba(26, 188, 156, 0.1);
        border: 1px solid rgba(26, 188, 156, 0.2);
        border-radius: 8px;
        padding: 0.8rem 1rem;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #1abc9c;
    }

    @media (max-width: 768px) {
        .rp-row {
            flex-direction: column;
        }

        .rp-group {
            min-width: 100%;
        }

        .rp-buttons {
            flex-direction: column !important;
        }
    }
</style>

<main id="content">
    <h1 class="page-title">Raporlar</h1>
    <p class="subtitle">Otopark hareketlerini detaylı olarak analiz edin.</p>

    <!-- Rapor Filtreleme Paneli -->
    <div class="rp-panel">
        <h2 class="rp-title"><i class="fas fa-chart-bar"></i> Rapor Filtreleme</h2>
        <form method="GET" action="" class="rp-form">
            <div class="rp-row">
                <div class="rp-group">
                    <label class="rp-label">Rapor Tipi</label>
                    <select name="report_type" class="rp-select">
                        <option value="daily" <?= $reportType == 'daily' ? 'selected' : '' ?>>Günlük Rapor</option>
                        <option value="weekly" <?= $reportType == 'weekly' ? 'selected' : '' ?>>Haftalık Rapor</option>
                        <option value="monthly" <?= $reportType == 'monthly' ? 'selected' : '' ?>>Aylık Rapor</option>
                    </select>
                </div>

                <div class="rp-group">
                    <label class="rp-label">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" class="rp-input" value="<?= htmlspecialchars($startDate) ?>">
                </div>

                <div class="rp-group">
                    <label class="rp-label">Bitiş Tarihi</label>
                    <input type="date" name="end_date" class="rp-input" value="<?= htmlspecialchars($endDate) ?>">
                </div>

                <div class="rp-group">
                    <label class="rp-label">Araç Tipi</label>
                    <select name="vehicle_type" class="rp-select">
                        <option value="all" <?= $vehicleType == 'all' ? 'selected' : '' ?>>Tüm Araç Tipleri</option>
                        <?php foreach ($vehicleTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $vehicleType == $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="rp-row">
                <div class="rp-group rp-buttons">
                    <button type="submit" class="rp-button rp-button-apply">
                        <i class="fas fa-filter"></i> Raporu Göster
                    </button>
                    <a href="?" class="rp-button rp-button-clear">
                        <i class="fas fa-times"></i> Filtreleri Temizle
                    </a>
                </div>
            </div>
        </form>

        <!-- Filtre Sonuçları -->
        <?php if ($reportType != 'daily' || $vehicleType != 'all' || $startDate != date('Y-m-d', strtotime('-7 days'))): ?>
            <div class="rp-results">
                <i class="fas fa-info-circle"></i>
                <?= count($reports) ?> kayıt bulundu |
                Rapor Tipi: <?= $reportType == 'daily' ? 'Günlük' : ($reportType == 'weekly' ? 'Haftalık' : 'Aylık') ?> |
                Tarih Aralığı: <?= date('d.m.Y', strtotime($startDate)) ?> - <?= date('d.m.Y', strtotime($endDate)) ?>
                <?= $vehicleType != 'all' ? '| Araç Tipi: ' . htmlspecialchars($vehicleType) : '' ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Özet Kartları -->
    <div class="summary-cards-grid">
        <div class="summary-card">
            <p>Toplam Araç</p>
            <h3><?= $summary['toplam_arac'] ?? 0 ?></h3>
        </div>
        <div class="summary-card">
            <p>İçerdeki Araç</p>
            <h3><?= $summary['icerdeki_arac'] ?? 0 ?></h3>
        </div>
        <div class="summary-card">
            <p>Toplam Ücret</p>
            <h3><?= number_format($summary['toplam_ucret'] ?? 0, 2, ',', '.') ?> ₺</h3>
        </div>
        <div class="summary-card">
            <p>Ortalama Günlük</p>
            <h3>
                <?php
                $days = max(1, round((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24)) + 1);
                $dailyAvg = ($summary['toplam_arac'] ?? 0) / $days;
                echo number_format($dailyAvg, 1);
                ?>
            </h3>
        </div>
    </div>

    <!-- Detaylı Rapor Tablosu -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Toplam Araç</th>
                    <th>İçerdeki Araç</th>
                    <th>Çıkan Araç</th>
                    <th>Toplam Ücret</th>
                    <th>Ortalama Ücret</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reports): ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <?php
                                if ($reportType === 'daily') {
                                    echo date('d.m.Y', strtotime($report['tarih']));
                                } elseif ($reportType === 'weekly') {
                                    echo $report['tarih'] . '. Hafta';
                                } else {
                                    echo date('F Y', strtotime($report['tarih']));
                                }
                                ?>
                            </td>
                            <td><?= $report['toplam_arac'] ?></td>
                            <td><?= $report['icerdeki_arac'] ?></td>
                            <td><?= $report['cikan_arac'] ?></td>
                            <td><?= number_format($report['toplam_ucret'] ?? 0, 2, ',', '.') ?> ₺</td>
                            <td>
                                <?php
                                $avg = $report['toplam_arac'] > 0 ? ($report['toplam_ucret'] ?? 0) / $report['toplam_arac'] : 0;
                                echo number_format($avg, 2, ',', '.') ?> ₺
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Seçilen kriterlere uygun rapor bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Grafik Alanı -->
    <div style="margin-top: 50px;" class="report-chart-section">
        <h2>Grafiksel Gösterim</h2>
        <div class="chart-container" style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 1rem;">
            <canvas id="reportChart"></canvas>
        </div>
    </div>

</main>

<script>
    // Tarih validasyonu
    document.querySelector('form').addEventListener('submit', function (e) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        if (startDate && endDate && startDate > endDate) {
            alert('Başlangıç tarihi bitiş tarihinden büyük olamaz!');
            e.preventDefault();
        }
    });

    // Varsayılan tarihleri ayarla
    function setDefaultDates() {
        const startDate = document.querySelector('input[name="start_date"]');
        const endDate = document.querySelector('input[name="end_date"]');

        if (!startDate.value) {
            startDate.value = '<?= date('Y-m-d', strtotime('-7 days')) ?>';
        }
        if (!endDate.value) {
            endDate.value = '<?= date('Y-m-d') ?>';
        }
    }

    // Sayfa yüklendiğinde tarihleri ayarla
    window.addEventListener('load', setDefaultDates);
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const reportLabels = <?= json_encode(array_map(function ($r) use ($reportType) {
        if ($reportType === 'daily') {
            return date('d.m.Y', strtotime($r['tarih']));
        } elseif ($reportType === 'weekly') {
            return $r['tarih'] . ". Hafta";
        } else {
            return date('F Y', strtotime($r['tarih']));
        }
    }, $reports)) ?>;

    const totalCarsData = <?= json_encode(array_column($reports, 'toplam_arac')) ?>;
    const totalFeesData = <?= json_encode(array_column($reports, 'toplam_ucret')) ?>;

    const ctx = document.getElementById('reportChart').getContext('2d');
    const reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: reportLabels,
            datasets: [
                {
                    label: 'Toplam Araç',
                    data: totalCarsData,
                    backgroundColor: 'rgba(26, 188, 156, 0.6)',
                    borderColor: 'rgba(26, 188, 156, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Toplam Ücret (₺)',
                    data: totalFeesData,
                    type: 'line',
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243,156,18,0.2)',
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Araç Sayısı' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Ücret (₺)' }
                }
            }
        }
    });
</script>


<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>