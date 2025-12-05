<?php 
    session_start();

    if (!isset($_SESSION['username'])){
        $_SESSION['msg'] = "You must log in first";
        header('location: login.php');
    }

    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['username']);
        header('location: login.php'); 
    }

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
  
</head>
<body>


    <div class="content">
        <!--การแจ้งเจือน -->
        <?php if (isset($_SESSION['username'])) : ?>
        <?php  endif ?>
            <div class="success">
                <h3>
                    <?php 
                        echo $_SESSION['success'] ;
                        unset($_SESSION['success']);
                    ?>
                </h3> 
            </div>   
        <!--เช็คล็อคอิน -->
        <?php if (isset($_SESSION['username'])): ?>
        <p> Hello<stong><?php echo $_SESSION['username']; ?></stong></p>
        <p><a href="index.php?logout='1'" style="color: red;" >logout</a></a></p>
        <?php endif ?>
    </div>

</body>
</html>