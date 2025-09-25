<aside class="sidebar">
    <nav>
        <ul>
            <li><a href="../home/index.php" class="<?php echo ($current_page == 'anasayfa') ? 'active' : ''; ?>"><i
                        class="fas fa-home"></i> Ana Sayfa</a></li>
            <li><a href="../followup/follow-up.php"
                    class="<?php echo ($current_page == 'arac-takibi') ? 'active' : ''; ?>"><i
                        class="fas fa-car-on"></i> Araç Takibi</a></li>
            <li><a href="../reports/reportlist.php"
                    class="<?php echo ($current_page == 'raporlar') ? 'active' : ''; ?>"><i
                        class="fas fa-chart-line"></i> Raporlar</a></li>
            <li><a href="../admins/adminlist.php"
                    class="<?php echo ($current_page == 'kullanicilar') ? 'active' : ''; ?>"><i
                        class="fas fa-user-group"></i> Kullanıcılar</a></li>
            <form id="logoutForm" action="../../islem/islem.php" method="post" style="display: none;">
                <input type="hidden" name="logout" value="1">
            </form>
            <li><a href="#" onclick="document.getElementById('logoutForm').submit(); return false;"><i
                        class="fas fa-right-from-bracket"></i> Çıkış Yap</a></li>
        </ul>
    </nav>
</aside>