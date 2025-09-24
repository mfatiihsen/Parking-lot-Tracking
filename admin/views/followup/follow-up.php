<?php

require_once '../../islem/conn.php';

?>


<?php
// Otopark içindeki araç sayısı
$stmt = $baglanti->query("SELECT COUNT(*) FROM cars WHERE situation = 'Giriş'");
$doluAlan = $stmt->fetchColumn();

// Toplam alan kapasitesi (örnek: 200)
$toplamAlan = 200;
?>

<?php
$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE entrance >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$gunIciGiris = $stmt->fetchColumn();
?>


<?php
$stmt = $baglanti->prepare("SELECT COUNT(*) FROM cars WHERE exit_time >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$odemeYapildi = $stmt->fetchColumn();
?>


<?php
// Bugünün tarihi
$today = date("Y-m-d");

// Bugünkü ödemelerin toplamını çekelim
$query = $baglanti->prepare("SELECT SUM(fee) as total_fee FROM cars WHERE DATE(exit_time) = :today");
$query->execute(['today' => $today]);
$total = $query->fetch(PDO::FETCH_ASSOC);
$todayTotalFee = $total['total_fee'] ?? 0;
?>



<?php
$search = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($search != "") {
    $query = $baglanti->prepare("SELECT * FROM cars 
                           WHERE plate LIKE :search 
                           OR situation LIKE :search 
                           ORDER BY entrance DESC");
    $query->execute(['search' => "%$search%"]);
} else {
    $query = $baglanti->query("SELECT * FROM cars ORDER BY entrance DESC");
}
$cars = $query->fetchAll(PDO::FETCH_ASSOC);
?>



<?php
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

    <div class="control-panel">
        <div class="search-box">
            <form method="GET" action="">
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Plaka veya durum ara..."
                    value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            </form>
        </div>
        <select class="status-filter">
            <option value="all">Tüm Araçlar</option>
            <option value="inside">Otopark İçinde</option>
            <option value="outside">Otopark Dışında</option>
            <option value="paid">Ödeme Yapmış</option>
        </select>
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
                <?php
                // Arama parametresini al
                $search = isset($_GET['q']) ? trim($_GET['q']) : "";

                if ($search != "") {
                    $sql = "SELECT * FROM cars 
                WHERE plate LIKE :search 
                OR situation LIKE :search 
                ORDER BY id DESC";
                    $stmt = $baglanti->prepare($sql);
                    $stmt->execute(['search' => "%$search%"]);
                } else {
                    $sql = "SELECT * FROM cars ORDER BY id DESC";
                    $stmt = $baglanti->prepare($sql);
                    $stmt->execute();
                }

                $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($cars):
                    foreach ($cars as $car): ?>
                        <tr>
                            <td><?= htmlspecialchars($car['plate']) ?></td>
                            <td><?= htmlspecialchars($car['type']) ?></td>
                            <td><?= htmlspecialchars($car['brand']) ?></td>
                            <td><?= date("H:i", strtotime($car['entrance'])) ?></td>
                            <td>
                                <?php if (!empty($car['exit_time']) && $car['exit_time'] != "0000-00-00 00:00:00"): ?>
                                    <?= date("H:i", strtotime($car['exit_time'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($car['fee'])): ?>
                                    <?= htmlspecialchars($car['fee']) ?> ₺
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
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="8">Sonuç bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</main>





<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>