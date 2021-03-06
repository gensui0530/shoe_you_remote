 <!-- footer -->
 <footer id="footer">
     Copyright <a href="">Shoe You</a>. All Rights Reserved.
 </footer>

 <script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
 <script>
     $(function() {
         var $ftr = $('#footer');
         if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
             $ftr.attr({
                 'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
             });
         }

         //メッセージ表示
         var $jsShowMsg = $('#js-show-msg');
         var msg = $jsShowMsg.text();
         if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
             $jsShowMsg.slideToggle('slow');
             setTimeout(function() {
                 $jsShowMsg.slideToggle('slow');
             }, 5000);
         }

         //画像ライブレビュー
         var $dropArea = $('.area-drop');
         var $fileInput = $('.input-file');

         $dropArea.on('dragover', function(e) {
             e.stopPropagation();
             e.preventDefault();
             $(this).css('border', '3px #ccc dashed');
         });

         $dropArea.on('dragleave', function(e) {
             e.stopPropagation();
             e.preventDefault();
             $(this).css('border', 'none');
         });
         $fileInput.on('change', function(e) {
             $dropArea.css('border', 'none');
             var file = this.files[0], //2.file配列にファイルが入っています
                 $img = $(this).siblings('.prev-img'), //3　JQueryのsiblingsメソッドで兄弟のimgを取得
                 fileReader = new FileReader(); //4 ファイルを読み込むFileReaderオブジェクト

             //5 読み込み完了した際のイベントハンドラ　imgのsrcにデータセット
             fileReader.onload = function(event) {
                 //読み込んだデータをimgに設定
                 $img.attr('src', event.target.result).show();
             };

             //6　画像の読み込み
             fileReader.readAsDataURL(file);

         });



         //テキストエリアカウント
         var $countUp = $('#js-count'),
             $countView = $('#js-count-view');
         $countUp.on('keyup', function(e) {
             $countView.html($(this).val().length);

         });



         //panel内文章の省略
         var count = 15;
         $('.panel-title').each(function() {
             var thisText = $(this).text();
             var textLength = thisText.length;
             if (textLength > count) {
                 var showText = thisText.substring(0, count);
                 var hideText = thisText.substring(count, textLength);
                 var insertText = showText;
                 insertText += '<span class="hide">' + hideText + '</span>';
                 insertText += '<span class="omit">...</span>';
                 $(this).html(insertText);

             };


         });

         //画面切替
         var $switchImgSubs = $('.js-switch-img-sub'),
             $switchImgMain = $('#js-switch-img-main');
         $switchImgSubs.on('click', function(e) {
             $switchImgMain.attr('src', $(this).attr('src'));

         });

         //お気に入り登録．削除
         var $like,
             likeProductId;
         $like = $('.js-click-like') || null;
         likeProductId = $like.data('productid') || null;
         if (likeProductId !== undefined && likeProductId !== null) {
             $like.on('click', function() {
                 var $this = $(this);
                 $.ajax({
                     type: "POST",
                     url: "ajaxLike.php",
                     data: {
                         productId: likeProductId
                     }
                 }).done(function(data) {
                     console.log('Ajax Success');
                     $this.toggleClass('active');
                 }).fail(function(msg) {
                     console.log('Ajax Error');
                 });

             });

         }



     });
 </script>
 </body>

 </html>