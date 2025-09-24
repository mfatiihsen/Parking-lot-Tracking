<?php

require_once '../../islem/conn.php';


$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

if ($searchQuery) {
    $admin = $baglanti->prepare("SELECT * FROM  admin WHERE name LIKE :search ORDER BY id ASC");
    $admin->bindValue(':search', '%' . $searchQuery . '%');
} else {
    $admin = $baglanti->prepare("SELECT * FROM admin ORDER BY id ASC");
}



$admin->execute();
$admins = $admin->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
$title = "Kullanıcılar";
ob_start();
?>



<main id="content">
    <h1 class="page-title">Yönetici Hesapları</h1>
    <p class="subtitle">Sistemdeki tüm yönetici hesaplarını buradan yönetin.</p>

    <div class="control-panel">

        <div class="search-box">
            <form action="#" method="GET">
                <i class="fas fa-search"></i>
                <input value="<?php echo htmlspecialchars($searchQuery) ?>" type="text" name="search" id="search"
                    placeholder="Yönetici ara...">
            </form>
        </div>

        <button onclick="window.location.href='adminadd.php'" class="add-button">
            <i class="fas fa-plus"></i> Yeni Giriş Ekle
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Soyad</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Yetki</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($admins) {
                    foreach ($admins as $admin) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['name']) ?></td>
                            <td><?php echo htmlspecialchars($admin['surname']) ?></td>
                            <td><?php echo htmlspecialchars($admin['username']) ?></td>
                            <td><?php echo htmlspecialchars($admin['mail']) ?></td>
                            <td><?php echo htmlspecialchars($admin['phone']) ?></td>
                            <td><?php echo htmlspecialchars($admin['authority']) ?></td>
                            <td>
                                <a href="../../islem/islem.php?adminsil&id=<?php echo $admin['id'] ?>">
                                    <button class='action-btn delete-btn'><i class='fas fa-trash-alt'></i></button></a>
                            </td>
                        </tr>

                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5'>Kayıtlı yönetici bulunamadı.</td></tr>";
                }

                ?>
            </tbody>
        </table>
    </div>
</main>



<?php
$content = ob_get_clean();
include('../../includes/_layout.php');
?>