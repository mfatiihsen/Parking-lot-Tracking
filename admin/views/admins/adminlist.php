<?php
$title = "Müşteriler";
ob_start();
?>



<main id="content">
    <h1 class="page-title">Yönetici Hesapları</h1>
    <p class="subtitle">Sistemdeki tüm yönetici hesaplarını buradan yönetin.</p>

    <div class="control-panel">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Yönetici ara...">
        </div>
        <button class="add-button">
            <i class="fas fa-plus"></i> Yeni Yönetici Ekle
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Yetki</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ahmet Yılmaz</td>
                    <td>ahmet.yilmaz@parkhub.com</td>
                    <td>Süper Admin</td>
                    <td><span class="status-active">Aktif</span></td>
                    <td>
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Ayşe Demir</td>
                    <td>ayse.demir@parkhub.com</td>
                    <td>Yönetici</td>
                    <td><span class="status-active">Aktif</span></td>
                    <td>
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Mehmet Öztürk</td>
                    <td>mehmet.ozturk@parkhub.com</td>
                    <td>Personel</td>
                    <td><span class="status-inactive">Pasif</span></td>
                    <td>
                        <button class="action-btn edit-btn"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete-btn"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</main>



<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>