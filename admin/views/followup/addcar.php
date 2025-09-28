<?php
$title = "Araç Takip";
ob_start();
?>

<main id="content">
    <h1 class="page-title">Yeni Araç Girişi</h1>
    <p class="subtitle">Otoparka yeni bir araç kaydı oluşturun veya mevcut bir aracın çıkışını yapın.</p>

    <div class="form-card">
        <form action="../../islem/islem.php" method="POST" class="entry-form">

            <div class="form-horizontal-group">
                <div class="form-group">
                    <label for="plate">Plaka</label>
                    <input type="text" id="plate" name="plate" placeholder="Örn: 34 ABC 123" required>
                </div>

                <div class="form-group">
                    <label for="brand">Marka / Model</label>
                    <input type="text" id="brand" name="brand" placeholder="Örn: Renault Clio">
                </div>

                <div class="form-group">
                    <label for="status">İşlem Durumu</label>
                    <select id="status" name="status" required>
                        <option value="" disabled selected>Durum seçin</option>
                        <option value="Giriş">Giriş</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">Araç Tipi</label>
                    <select id="type" name="type" required>
                        <option value="" disabled selected>Araç Tipi seçin</option>
                        <option value="Otomobil">Otomobil</option>
                        <option value="SUV">SUV</option>
                        <option value="Panelvan">Panelvan</option>
                        <option value="Ticari">Ticari</option>
                    </select>
                </div>
            </div>

            <!-- GİRİŞ ZAMANI BİLGİSİ (SADECE GÖSTERİM İÇİN) -->
            <div class="form-group">
                <label for="entry_datetime_visible">Giriş Tarih ve Saati (Otomatik)</label>
                <input type="text" id="entry_datetime_visible" readonly style="background-color: #f8f9fa; color: #666;">

                <!-- GİZLİ ALAN YERİNE PHP TARAFINDA OTOMATİK OLUŞTUR -->
                <input type="hidden" id="entry_datetime" name="entry_datetime"
                    value="<?php echo date('Y-m-d H:i:s'); ?>">
            </div>

            <div class="form-actions">
                <button name="caradd" type="submit" class="add-button">
                    <i class="fas fa-car"></i> Kaydet
                </button>
                <button type="button" class="cancel-button" onclick="history.back()">
                    <i class="fas fa-ban"></i> İptal
                </button>
            </div>
        </form>
    </div>
</main>
<script>
    document.getElementById('plate').addEventListener('input', function (e) {
        let val = e.target.value.toUpperCase();   // Harfleri büyüt
        val = val.replace(/\s+/g, '');            // Tüm boşlukları kaldır (önce temizle)

        // TR plaka formatına uydur: 2 rakam + 1-3 harf + 2-4 rakam
        const match = val.match(/^(\d{1,2})([A-Z]{1,3})(\d{1,4})$/);

        if (match) {
            // Format: 34 ABC 1234
            val = `${match[1]} ${match[2]} ${match[3]}`;
        }

        e.target.value = val;  // Düzenlenmiş halini input'a yaz
    });
</script>
<script>
    // Sayfa yüklendiğinde şu anki zamanı göster
    document.addEventListener('DOMContentLoaded', function () {
        const now = new Date();
        const formattedDate = now.toLocaleString('tr-TR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        document.getElementById('entry_datetime_visible').value = formattedDate;
        document.getElementById('entry_datetime').value = now.toISOString().slice(0, 19).replace('T', ' ');
    });
</script>

<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>