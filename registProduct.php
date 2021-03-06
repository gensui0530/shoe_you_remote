<?php

//共通変数/関数ファイルを読み込み
require('function.php');

debug('=================================================================');
debug('商品出品登録ページ');
debug('=================================================================');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
//画面処理
//================================

//画面表示用データ取得
//================================
// GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
//新規登録画面か編集画面か判別フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
debug('商品ID：' . $p_id);
debug('フォーム用DBデータ：' . print_r($dbFormData, true));
debug('カテゴリーデータ' . print_r($dbCategoryData, true));

//パラメータ改ざんチェック
//====================================
// GETパラメータはあるが，改ざんされている（URLを弄った）場合，正しい商品データが取れないのでマイページへ遷移させる
if (!empty($p_id) && empty($dbFormData)) {
    debug('GETパラメータの商品IDが違います．マイページ遷移します');
    header("Location:mypage.php"); //マイページへ
}

//post送信時処理
//===================================
if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報:' . print_r($_POST, true));
    debug('FILE情報:' . print_r($_FILES, true));

    //変数にユーザ情報を代入
    $name = $_POST['name'];
    $category = $_POST['category_id'];
    $p_size = $_POST['size_id'];
    $price = (!empty($_POST['price'])) ? $_POST['price'] : 0; //0やから文字の場合は０をいれる
    $comment = $_POST['comment'];

    //画像をアップロードし，パスを格納
    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
    //画像をPOSTしてない（登録してない）が既にDBに登録されている場合，DBのパスを入れる（POSTに反映されないため）
    $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
    $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
    $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
    $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
    $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

    //更新の場合はDB情報と入力情報が異なる場合にバリデーションを行う
    if (empty($dbFormData)) {
        //未入力チェック
        validRequired($name, 'name');
        //最大文字数チェック
        validMaxLen($name, 'name');
        //セレクトボックスチェック
        validSelect($category, 'category_id');
        //最大文字数チェック
        validMaxLen($comment, 'comment', 500);
        //未入力チェック
        validRequired($price, 'price');
        //半角数字チェック
        validNumber($price, 'price');
        //未入力チェック
        validRequired($p_size, 'size_id');
    } else {
        if ($dbFormData['name'] !== $name) {
            //未入力チェック
            validRequired($name, 'name');
            //最大文字数チェック
            validMaxLen($name, 'name');
        }
        if ($dbFormData['category_id'] !== $category) {
            //セレクトボックスチェック
            validSelect($category, 'category_id');
        }

        if ($dbFormData['comment'] !== $comment) {
            //最大文字数チェック
            validMaxLen($comment, 'comment', 500);
        }

        if ($dbFormData['price'] != $price) {
            //未入力チェック
            validRequired($price, 'price');
            //半角数字チェック
            validNumber($price, 'price');
        }

        if ($dbFormData['size_id'] != $p_size) {
            //未入力チェック
            validRequired($p_size, 'size_id');
            //半角数字チェック
            validNumber($p_size, 'size_id');
        }
    }

    if (empty($err_msg)) {
        debug('バリデーションOKです．');


        //例外処理
        try {
            //DBhe接続
            $dbh = dbConnect();
            //SQL文作成
            //編集画面の場合はUPDATE文，新規登録画面の場合はINSERT文を生成
            if ($edit_flg) {
                debug('DB更新です');
                $sql = 'UPDATE shoes SET name = :name, category_id = :category, price = :price, size_id = :p_size, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
                $data = array(':name' => $name, ':category' => $category, ':price' => $price, ':p_size' => $p_size, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
            } else {
                debug('DB新規登録です');
                $sql = 'INSERT INTO shoes (name, category_id, price, size_id, comment, pic1, pic2, pic3, user_id, create_date) VALUES(:name, :category, :price , :p_size, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
                $data = array(':name' => $name, ':category' => $category, ':price' => $price, ':p_size' => $p_size, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
            }
            debug('SQL:' . $sql);
            debug('流し込みデータ:' . print_r($data, true));
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);

            //クエリ成功の場合
            if ($stmt) {
                $_SESSION['msg_success'] = SUC04;
                debug('マイページ遷移します．');
                header("Location:mypage.php"); //マイページへ
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理完了　<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (!$edit_flg) ? '商品出品登録' : '商品編集';
require('head.php');
?>

<body class="page-profEdit page-2colum">

    <!-- メニュー　-->
    <?php
    require('header.php');
    ?>

    <?php
    require('navbar.php');
    ?>
    <!-- メインコンテンツ　-->
    <div id="contents" class="site-width">

        <!-- メイン　-->
        <section id="main">
            <div class="form-container">
                <h1 class="page-title" style="margin-right:20px;">
                    <?php echo (!$edit_flg) ? '商品の情報を入力' : '商品の情報を編集'; ?>
                </h1>
                <form class="form" method="post" enctype="multipart/form-data" style="margin-left:90px; height:1050px; width:800px; box-sizing:border-box;">
                    <div class="area-msg">
                        <?php
                        if (!empty($err_msg['common'])) echo $err_msg['common'];
                        ?>
                    </div>

                    <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>">
                        商品名　<span class="label-require">必須</span>
                        <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
                    </label>
                    <div class="area-msg">
                        <?php
                        if (!empty($err_msg['name'])) echo $err_msg['name'];
                        ?>
                    </div>

                    <label class="<?php if (!empty($err_msg['category_id'])) echo 'err'; ?>">
                        カテゴリー　<span class="label-require">必須</span>
                        　　<select name="category_id">
                            <option value="0" <?php if (getFormData('category_id') == 0) {
                                                    echo 'selected';
                                                } ?>>選択してください</option>
                            <?php
                            foreach ($dbCategoryData as $key => $val) {
                            ?>
                                <option value="<?php echo $val['id'] ?>" <?php if (getFormData('category_id') == $val['id']) {
                                                                                echo 'selected';
                                                                            } ?>>
                                    <?php echo $val['name']; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </label>
                    <div class="area-msg">
                        <?php
                        if (!empty($err_msg['category_id'])) echo $err_msg['category_id'];
                        ?>
                    </div>

                    <div class=" area-msg">
                        <?php
                        if (!empty($err_msg['size'])) echo $err_msg['size']; ?>
                    </div>

                    <label class="<?php if (!empty($err_msg['size_id'])) echo 'err'; ?>" style="width:20%;">
                        Size 　必須
                        <div style="display: inline-flex">
                            <input class="size_id" type="text" name="size_id" placeholder="25.0" value="<?php echo getFormData('size_id'); ?>">
                            <span style="height:10%;  margin:20px 0px 0px 10px;">㎝</span>
                        </div>
                    </label>
                    <div class=" area-msg">
                        <?php
                        if (!empty($err_msg['size_id'])) echo $err_msg['size_id']; ?>
                    </div>

                    <div style=" margin-left:28px; margin-top:20px; margin-bottom:30px; overflow: hidden;">
                        <div class="imgDrop-container">
                            画像1
                            <label style="margin-left:0px;" class="area-drop <?php if (!empty($err_msg['pic1'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic1" class="input-file prof-input-file">
                                <img src="<?php echo getFormData('pic1'); ?>" class=" prof-img prev-img" style="<?php if (empty(getFormData('pic1'))) echo 'display:none;' ?>">
                                ドラック＆ドロップ
                            </label>
                            <div class="area-msg">
                                <?php
                                if (!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                                ?>
                            </div>
                        </div>

                        <div class="imgDrop-container">
                            画像2
                            <label style="margin-left:0px;" class="area-drop <?php if (!empty($err_msg['pic2'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic2" class="input-file">
                                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic2'))) echo 'display:none;' ?>">
                                ドラック＆ドロップ
                            </label>
                            <div class="area-msg">
                                <?php
                                if (!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                                ?>
                            </div>
                        </div>

                        <div class="imgDrop-container">
                            画像3
                            <label style="margin-left:0px;" class="area-drop <?php if (!empty($err_msg['pic3'])) echo 'err'; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                                <input type="file" name="pic3" class="input-file">
                                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic3'))) echo 'display:none;' ?>">
                                ドラック＆ドロップ
                            </label>
                            <div class="area-msg">
                                <?php
                                if (!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                                ?>
                            </div>
                        </div>
                    </div>
                    <label class="<?php if (!empty($err_msg['comment'])) echo 'err' ?>">
                        詳細
                        <textarea name="comment" id="js-count" rows="10" cols="30" style="height:150px; border:none; background:#eaddcf;"><?php echo getFormData('comment'); ?></textarea>
                    </label>
                    <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
                    <div class="area-msg">
                        <?php
                        if (!empty($err_msg['comment'])) echo $err_msg['comment'];
                        ?>
                    </div>

                    <label style="text-align:left;" class="<?php if (!empty($err_msg['price'])) echo 'err'; ?>">
                        金額 <span class="label-require">必須</span>
                        <div class="form-group">
                            <input type="text" name="price" style="width:150px" placeholder="50,000" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>"><span class="option">円</span>
                        </div>
                    </label>
                    <div class="area-msg">
                        <?php
                        if (!empty($err_msg['price'])) echo $err_msg['price'];
                        ?>
                    </div>

                    <div class="btn-container">
                        <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '出品する' : '更新する'; ?>" style=" margin-top:60px; margin-right:300px;">
                        　</div>




                </form>
            </div>
        </section>
    </div>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>