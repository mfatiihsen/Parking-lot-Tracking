<header class="navbar">
    <div class="navbar-logo">
        <i class="fas fa-car-space"></i>
        <span style="color:white">PARK<b class="highlight">HUB</b></span>
    </div>

    <div class="navbar-utility">
        <div id="live-clock" class="navbar-datetime">
            <i class="far fa-clock"></i>
            <span></span>
        </div>

        <div class="navbar-profile">
            <div class="user-info">
                <span style="color:white"><?php
                if (isset($_SESSION['adminname'])) {
                    echo "Hoşgeldin Admin!";
                } else {
                    echo "Hoş Geldin," . htmlspecialchars($_SESSION['name']) . "!";
                }
                ?></span>
                <small><?php
                if (isset($_SESSION['authority'])) {
                    echo $_SESSION['authority'];
                } else {
                    echo "Bilinmiyor";
                }
                ?></small>
            </div>
            <img src="https://w7.pngwing.com/pngs/961/445/png-transparent-person-pinterest-profile-user-pinterest-ui-colored-icon-thumbnail.png"
                alt="User Avatar" class="user-avatar">
            <br>
        </div>
    </div>
</header>