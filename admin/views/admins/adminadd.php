<?php
$title = "Kullanıcılar";
ob_start();
?>




<main id="content">
    <h1 class="page-title">Yeni Giriş Ekle</h1>
    <p class="subtitle">Yeni bir yönetici hesabı oluşturmak için aşağıdaki formu kullanın.</p>

    <div class="form-card">
        <form action="../../islem/islem.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="name">Ad </label>
                <input type="text" id="name" name="name" placeholder="Ad " required>
            </div>

            <div class="form-group">
                <label for="surname">Soyad </label>
                <input type="text" id="surname" name="surname" placeholder="Soyad" required>
            </div>


            <div class="form-group">
                <label for="username">Kullanıcı Adı </label>
                <input type="text" id="username" name="username" placeholder="Kullanıcı Adı" required>
            </div>


            <div class="form-group">
                <label for="phone">Telefon Numarası</label>
                <input type="text" id="phone" name="phone" placeholder="Telefon Numarası" required>
            </div>

            <div class="form-group">
                <label for="email">E-posta Adresi</label>
                <input type="email" id="email" name="email" placeholder="örnek@eposta.com" required>
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" placeholder="Yeni şifre" required>
            </div>

            <div class="form-group">
                <label for="password-confirm">Şifre Tekrar</label>
                <input type="password" id="password-confirm" name="password-confirm" placeholder="Şifreyi tekrar girin"
                    required>
            </div>

            <div class="form-group">
                <label for="authority">Yönetici Yetkisi</label>
                <select id="authority" name="authority" required>
                    <option value="" disabled selected>Yetki seçin</option>
                    <option value="Yönetici">Yönetici</option>
                    <option value="Personel">Personel</option>
                </select>
            </div>

            <div class="form-actions">
                <button name="adminadd" type="submit" class="add-button">
                    <i class="fas fa-user-plus"></i> Yöneticiyi Kaydet
                </button>
                <button type="button" class="cancel-button">
                    <i class="fas fa-ban"></i> İptal
                </button>
            </div>
        </form>
    </div>
</main>


<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>