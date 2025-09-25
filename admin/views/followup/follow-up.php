<?php

require_once '../../islem/conn.php';

// Otopark içindeki araç sayısı
$stmt = $baglanti->query("SELECT COUNT(*) FROM cars WHERE situation = 'Giriş'");
$doluAlan = $stmt->fetchColumn();

// Toplam alan kapasitesi (örnek: 200)
$toplamAlan = 200;

$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE entrance >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$gunIciGiris = $stmt->fetchColumn();

$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE exit_time >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$odemeYapildi = $stmt->fetchColumn();

// Bugünün tarihi
$today = date("Y-m-d");

// Bugünkü ödemelerin toplamını çekelim
$query = $baglanti->prepare("SELECT SUM(fee) as total_fee FROM cars WHERE DATE(exit_time) = :today");
$query->execute(['today' => $today]);
$total = $query->fetch(PDO::FETCH_ASSOC);
$todayTotalFee = $total['total_fee'] ?? 0;

// Filtreleme ve arama parametrelerini al
$search = isset($_GET['q']) ? trim($_GET['q']) : "";
$status = $_GET['status'] ?? "all";
$vehicleType = $_GET['vehicle_type'] ?? "all";
$dateFilter = $_GET['date_filter'] ?? "all";
$customDate = $_GET['custom_date'] ?? "";

// SQL sorgusunu oluştur
$sql = "SELECT * FROM cars WHERE 1";
$params = [];

// Durum filtresi
if ($status === "inside") {
    $sql .= " AND situation = ?";
    $params[] = "Giriş";
} elseif ($status === "outside") {
    $sql .= " AND situation = ?";
    $params[] = "Çıkış";
} elseif ($status === "paid") {
    $sql .= " AND fee IS NOT NULL AND fee > 0";
}

// Araç tipi filtresi
if ($vehicleType !== "all") {
    $sql .= " AND type = ?";
    $params[] = $vehicleType;
}

// Tarih filtresi
if ($dateFilter === "today") {
    $sql .= " AND DATE(entrance) = CURDATE()";
} elseif ($dateFilter === "yesterday") {
    $sql .= " AND DATE(entrance) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($dateFilter === "week") {
    $sql .= " AND entrance >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateFilter === "month") {
    $sql .= " AND entrance >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
} elseif ($dateFilter === "custom" && !empty($customDate)) {
    $sql .= " AND DATE(entrance) = ?";
    $params[] = $customDate;
}

// Arama
if ($search != "") {
    $sql .= " AND (plate LIKE ? OR brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";

$stmt = $baglanti->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Araç tiplerini al (filtre için)
$vehicleTypesStmt = $baglanti->query("SELECT DISTINCT type FROM cars WHERE type IS NOT NULL AND type != ''");
$vehicleTypes = $vehicleTypesStmt->fetchAll(PDO::FETCH_COLUMN);

$title = "Araç Takip";
ob_start();
?>

<main id="content">
    <h1 class="page-title">Araç Takibi</h1>
    <p class="subtitle">Otopark içindeki ve dışındaki araçları anlık olarak izleyin.</p>

    <div class="kpi-widgets-grid">
        <div class="widget">
            <div class="widget-icon"><i class="fas fa-car-on"></i></div>
            <div class="widget-info">
                <h3>Dolu Alan</h3>
                <p class="data"><b><?= $doluAlan ?></b></p>
                <p class="sub-data">toplam <?= $toplamAlan ?> alandan</p>
            </div>
        </div>
        <div class="widget">
            <div class="widget-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="widget-info">
                <h3>Gün İçinde Giriş</h3>
                <p class="data"><b><?= $gunIciGiris ?></b></p>
                <p class="sub-data">son 24 saat</p>
            </div>
        </div>
        <div class="widget">
            <div class="widget-icon"><i class="fas fa-circle-check"></i></div>
            <div class="widget-info">
                <h3>Ödeme Yapıldı</h3>
                <p class="data"><b><?= $odemeYapildi ?></b></p>
                <p class="sub-data">son 24 saat</p>
            </div>
        </div>
        <div class="widget">
            <div class="widget-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="widget-info">
                <h3>Bugün Ödeme</h3>
                <p class="data"><b><?= number_format($todayTotalFee, 2, ',', '.') ?> ₺</b></p>
                <p class="sub-data">toplam tutar</p>
            </div>
        </div>
    </div>

    <div class="shortcut-panel">
        <h2 class="shortcut-title">Yeni Giriş</h2>
        <a href="../followup/addcar.php" class="shortcut-button">
            <i class="fas fa-plus-circle"></i>
            <span>Yeni Araç Girişi</span>
        </a>
    </div>

    <!-- Geliştirilmiş Filtreleme Paneli -->
    <div class="fp-panel">
        <h2 class="fp-title">
            <i class="fas fa-filter"></i>Filtreleme Seçenekleri
        </h2>
        <form method="GET" action="" class="fp-form">
            <div class="fp-row">
                <div class="fp-group">
                    <label for="fp-search" class="fp-label">Arama</label>
                    <div class="fp-search-box">
                        <i class="fas fa-search fp-search-icon"></i>
                        <input type="text" name="q" id="fp-search" class="fp-search-input"
                            placeholder="Plaka veya marka ara..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>

                <div class="fp-group">
                    <label for="fp-status" class="fp-label">Durum</label>
                    <select name="status" id="fp-status" class="fp-select">
                        <option value="all" <?= ($status == 'all') ? 'selected' : '' ?>>Tüm Araçlar</option>
                        <option value="inside" <?= ($status == 'inside') ? 'selected' : '' ?>>Otopark İçinde</option>
                        <option value="outside" <?= ($status == 'outside') ? 'selected' : '' ?>>Otopark Dışında</option>
                        <option value="paid" <?= ($status == 'paid') ? 'selected' : '' ?>>Ödeme Yapmış</option>
                    </select>
                </div>

                <div class="fp-group">
                    <label for="fp-vehicle-type" class="fp-label">Araç Tipi</label>
                    <select name="vehicle_type" id="fp-vehicle-type" class="fp-select">
                        <option value="all" <?= ($vehicleType == 'all') ? 'selected' : '' ?>>Tüm Tipler</option>
                        <?php foreach ($vehicleTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($vehicleType == $type) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="fp-row">
                <div class="fp-group">
                    <label for="fp-date-filter" class="fp-label">Tarih Filtresi</label>
                    <select name="date_filter" id="fp-date-filter" class="fp-select" onchange="fpToggleCustomDate()">
                        <option value="all" <?= ($dateFilter == 'all') ? 'selected' : '' ?>>Tüm Zamanlar</option>
                        <option value="today" <?= ($dateFilter == 'today') ? 'selected' : '' ?>>Bugün</option>
                        <option value="yesterday" <?= ($dateFilter == 'yesterday') ? 'selected' : '' ?>>Dün</option>
                        <option value="week" <?= ($dateFilter == 'week') ? 'selected' : '' ?>>Son 7 Gün</option>
                        <option value="month" <?= ($dateFilter == 'month') ? 'selected' : '' ?>>Son 30 Gün</option>
                        <option value="custom" <?= ($dateFilter == 'custom') ? 'selected' : '' ?>>Özel Tarih</option>
                    </select>
                </div>

                <div class="fp-group fp-custom-date-group" id="fp-custom-date-group"
                    style="<?= ($dateFilter == 'custom') ? '' : 'display: none;' ?>">
                    <label for="fp-custom-date" class="fp-label">Özel Tarih</label>
                    <input type="date" name="custom_date" id="fp-custom-date"
                        value="<?= htmlspecialchars($customDate) ?>" class="fp-input">
                </div>

                <div class="fp-group fp-buttons">
                    <button type="submit" class="fp-button fp-button-apply">
                        <i class="fas fa-filter"></i> Filtrele
                    </button>
                    <a href="?" class="fp-button fp-button-clear">
                        <i class="fas fa-times"></i> Temizle
                    </a>
                </div>
            </div>
        </form>

        <!-- Filtre Sonuçları -->
        <?php if ($search != "" || $status != "all" || $vehicleType != "all" || $dateFilter != "all"): ?>
            <div class="fp-results">
                <i class="fas fa-filter fp-results-icon"></i>
                <span>
                    <?= count($cars) ?> araç bulundu
                    <?php
                    $filters = [];
                    if ($search != "")
                        $filters[] = "aranan: '" . htmlspecialchars($search) . "'";
                    if ($status != "all")
                        $filters[] = "durum: " . ($status == "inside" ? "Otopark İçinde" : ($status == "outside" ? "Otopark Dışında" : "Ödeme Yapmış"));
                    if ($vehicleType != "all")
                        $filters[] = "araç tipi: " . htmlspecialchars($vehicleType);
                    if ($dateFilter != "all")
                        $filters[] = "tarih: " . ($dateFilter == "today" ? "Bugün" : ($dateFilter == "yesterday" ? "Dün" : ($dateFilter == "week" ? "Son 7 Gün" : ($dateFilter == "month" ? "Son 30 Gün" : "Özel Tarih"))));

                    if (!empty($filters)) {
                        echo "(" . implode(", ", $filters) . ")";
                    }
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Plaka</th>
                    <th>Araç Tipi</th>
                    <th>Marka</th>
                    <th>Giriş Saati</th>
                    <th>Çıkış Saati</th>
                    <th>Ödenen Ücret</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cars): ?>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><?= htmlspecialchars($car['plate']) ?></td>
                            <td><?= htmlspecialchars($car['type']) ?></td>
                            <td><?= htmlspecialchars($car['brand']) ?></td>
                            <td><?= date("d.m.Y H:i", strtotime($car['entrance'])) ?></td>
                            <td>
                                <?php if (!empty($car['exit_time']) && $car['exit_time'] != "0000-00-00 00:00:00"): ?>
                                    <?= date("d.m.Y H:i", strtotime($car['exit_time'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($car['fee'])): ?>
                                    <?= number_format($car['fee'], 2, ',', '.') ?> ₺
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($car['situation'] == 'Giriş'): ?>
                                    <span class="status-inside">Otopark İçinde</span>
                                <?php else: ?>
                                    <span class="status-outside">Dışarıda</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="../../islem/islem.php" method="post" style="margin:0;">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    <button type="submit" class="otopark-cikis-butonu"
                                        style="display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; background-color: transparent; border: 1px solid #FF5722; color: #FF5722; transition: all 0.3s ease;">
                                        Çıkış Yap
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Filtre kriterlerine uygun araç bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    function toggleCustomDate() {
        const dateFilter = document.getElementById('date_filter');
        const customDateGroup = document.getElementById('custom-date-group');

        if (dateFilter.value === 'custom') {
            customDateGroup.style.display = 'block';
        } else {
            customDateGroup.style.display = 'none';
        }
    }
</script>


<style>
    /* Filtreleme Paneli Özel Stilleri */
    .fp-panel {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .fp-title {
        font-size: 1.2rem;
        color: #f0f2f5;
        margin-bottom: 1.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .fp-title i {
        color: #1abc9c;
    }

    .fp-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .fp-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: flex-end;
    }

    .fp-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }

    .fp-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #b0c4de;
        font-size: 0.9rem;
    }

    .fp-select,
    .fp-input {
        padding: 0.8rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #f0f2f5;
        font-family: "Poppins", sans-serif;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .fp-select:focus,
    .fp-input:focus {
        outline: none;
        border-color: #1abc9c;
        box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.2);
    }

    .fp-select option {
        background: #0a1728;
        color: #f0f2f5;
    }

    /* Filtre Butonları */
    .fp-buttons {
        flex-direction: row !important;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .fp-button {
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
        font-family: "Poppins", sans-serif;
    }

    .fp-button-apply {
        background: #1abc9c;
        color: #0b1a2e;
    }

    .fp-button-apply:hover {
        background: #16a085;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 188, 156, 0.3);
    }

    .fp-button-clear {
        background: rgba(255, 255, 255, 0.1);
        color: #f0f2f5;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .fp-button-clear:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    /* Arama Kutusu */
    .fp-search-box {
        position: relative;
    }

    .fp-search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #1abc9c;
        z-index: 1;
    }

    .fp-search-input {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 3rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #f0f2f5;
        font-family: "Poppins", sans-serif;
    }

    .fp-search-input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .fp-search-input:focus {
        outline: none;
        border-color: #1abc9c;
    }

    /* Özel Tarih Grubu */
    .fp-custom-date-group {
        transition: all 0.3s ease;
    }

    /* Filtre Sonuçları */
    .fp-results {
        background: rgba(26, 188, 156, 0.1);
        border: 1px solid rgba(26, 188, 156, 0.2);
        border-radius: 8px;
        padding: 0.8rem 1rem;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #1abc9c;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .fp-results-icon {
        font-size: 1rem;
    }

    /* Mobil Duyarlı Tasarım */
    @media (max-width: 768px) {
        .fp-panel {
            padding: 1rem;
        }

        .fp-row {
            flex-direction: column;
            gap: 1rem;
        }

        .fp-group {
            min-width: 100%;
        }

        .fp-buttons {
            flex-direction: column !important;
        }

        .fp-button {
            justify-content: center;
            width: 100%;
        }
    }

    /* Durum Göstergesi */
    .fp-status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .fp-status-active {
        background-color: rgba(26, 188, 156, 0.2);
        color: #1abc9c;
    }

    .fp-status-inactive {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }
</style>


<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>