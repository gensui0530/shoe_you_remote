<?php
//共有変数・関数ファイルを読み込み
require('function.php');

debug('===========================================================');
debug('マイページ');
debug('===========================================================');
debugLogStart();

//ログイン認証
require('auth.php'); 

?>


<?php
$siteTitle = 'Sign up';
require('head.php');
?>

<body class="page-signup page-1colum">


    <?php
    require('header.php');
    ?>


    <?php
    require('footer.php');
    ?>