<?php
require_once '../../auth/checkAuth.php';
 ?>


<!DOCTYPE html>
<html lang="tr">
<?php include("_head.php"); ?>

<body>

    <div class="admin-container">

        <?php include('_navbar.php'); ?>

        <?php include('_sidebar.php'); ?>

        <section id="content">
            <?php echo $content; ?>
        </section>

    </div>

    <?php include('_script.php'); ?>

</body>

</html>