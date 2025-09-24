<?php
include('../../includes/db.php'); // PDO bağlantınız

$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate = $_GET['end'] ?? date('Y-m-d');

// Günlük Doluluk
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

// Günlük doluluk saatlik
$dolulukSaatlik = [];
for ($hour = 0; $hour < 24; $hour++) {
    $start = sprintf("%02d:00:00", $hour);
    $end = sprintf("%02d:59:59", $hour);
    $stmt = $baglanti->prepare("SELECT COUNT(*) as count FROM cars WHERE DATE(entrance) BETWEEN :startDate AND :endDate AND TIME(entrance) BETWEEN :start AND :end AND situation='Giriş'");
    $stmt->execute(['startDate' => $startDate, 'endDate' => $endDate, 'start' => $start, 'end' => $end]);
    $dolulukSaatlik[] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Araç Giriş/Çıkış Raporu
$stmt = $baglanti->prepare("SELECT plate, type, brand, entrance, exit_time, fee, situation FROM cars WHERE DATE(entrance) BETWEEN :start AND :end ORDER BY entrance DESC");
$stmt->execute(['start' => $startDate, 'end' => $endDate]);
$aracRapor = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'toplamGiris' => $toplamGiris,
    'toplamCikis' => $toplamCikis,
    'toplamGelir' => $toplamGelir,
    'ortalamaDoluluk' => $ortalamaDoluluk,
    'dolulukSaatlik' => $dolulukSaatlik,
    'aracRapor' => $aracRapor
]);
