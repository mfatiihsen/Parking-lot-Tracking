<?php
$title = "Raporlar";
ob_start();
?>




<main id="content">
    <h1 class="page-title">Raporlar ve İstatistikler</h1>
    <p class="subtitle">Otoparkınızın performansını detaylı raporlarla analiz edin.</p>

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
            <input type="date" id="date-range" class="report-date-input">
        </div>
        <button class="generate-report-btn">
            <i class="fas fa-chart-line"></i> Rapor Oluştur
        </button>
    </div>

    <div class="report-chart-section">
        <h2>Seçilen Rapor: Günlük Doluluk Raporu</h2>
        <div class="chart-container">
            <div class="chart-placeholder large-chart">
                <p>Grafik alanı</p>
            </div>
        </div>
    </div>

    <div class="report-summary-section">
        <h2>Rapor Özeti</h2>
        <div class="summary-cards-grid">
            <div class="summary-card">
                <p>Ortalama Doluluk</p>
                <h3>72%</h3>
            </div>
            <div class="summary-card">
                <p>Toplam Giriş</p>
                <h3>550</h3>
            </div>
            <div class="summary-card">
                <p>Toplam Çıkış</p>
                <h3>530</h3>
            </div>
            <div class="summary-card">
                <p>Toplam Gelir</p>
                <h3>15,200 TL</h3>
            </div>
        </div>
    </div>

</main>





<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>