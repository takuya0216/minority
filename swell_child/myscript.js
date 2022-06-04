//カスタマイズ用スクリプト
// 最初に、ビューポートの高さを取得し、0.01を掛けて1%の値を算出して、vh単位の値を取得
let vh = window.innerHeight * 0.01;
// カスタム変数--vhの値をドキュメントのルートに設定
document.documentElement.style.setProperty('--vh', `${vh}px`);

// ビューポートリサイズ
window.addEventListener('resize', () => {

  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
});

jQuery(function ($) {

  const scrollTime = 700;
  $('.fade-in-up').each(function () {
    if (is_in_sight($(this))) {
      $(this).addClass('effect-scroll');
    }
  });
  /* window scroll function start*/
  $(window).scroll(function () {
    scroll_effect();
  })
  /* window scroll function end*/

  /* コンタクトフォームsubmit load function end*/
  $(document).on('click', 'button.wpcf7cp-cfm-edit-btn, button.wpcf7cp-cfm-submit-btn', function () {
    $('.wpcf7cpcnf-title').css('display', 'none');
  });

  /*トップメインビジュアル スクロールボタン矢印*/
  $('p-mainVisual__scrollArrow').empty();
  $('.p-mainVisual__scroll').html('<svg class="p-mainVisual__scrollArrow" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 34" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><line x1="11" y1="0" x2="11" y2="29"></line></svg><span class="p-mainVisual__scrollLabel">Scroll</span>');

  /*トップへ戻るボタン スクロール*/
  $('#pagetop').on('click', function () {
    $("html").animate({ scrollTop: 0 }, { duration: scrollTime, easing: 'swing', });
  });

  /*インスタグラムフィードのボタンテキスト変更*/
  $('.sbi_btn_text').text("Load More...");
  /*インスタグラムフィード画像にフェードイン付加*/
  $('.sbi_item').each(function () {
    $(this).addClass('fade-in-up');
  });

  /*日付カレンダー設定*/
  //選択付加の日付。「yyyy/mm/dd」形式でコンマ区切りで指定。
  var disableDates = [
    "2022/06/06",
    "2022/06/14"
  ];
  $("#cf7-pickup-date").datepicker({
    dateFormat: 'yy/mm/dd',
    yearSuffix: '年',
    showMonthAfterYear: true,
    monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
    dayNames: ['日', '月', '火', '水', '木', '金', '土'],
    dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
    minDate: "+5d",
    firstDay: 0,
    beforeShowDay: function (date) {

      // 土日選択不可
      if (date.getDay() == 0 || date.getDay() == 6) {
        return [false, ''];
      }
      // 特定日を選択できないようにする
      var disableDate = $.datepicker.formatDate('yy/mm/dd', date);
      if (disableDates.indexOf(disableDate) == 1) {
        return [false, ''];
      }
      // それ以外
      return [true, ''];
    },
  });
  $("#Date").datepicker("setDate", "+5d");

  //スクロールアニメーション
  function scroll_effect() {
    //fade-in-up
    $('.fade-in-up').each(function () {
      //.fade-in-upを指定したエレメントのBottom位置がトリガー。
      var elemPos = $(this).offset().top + $(this).outerHeight() - 100;
      var scroll = $(window).scrollTop();
      var windowHeight = $(window).height();
      if (elemPos < scroll + windowHeight) {
        $(this).addClass('effect-scroll');
      }
    });
  }
  //要素の画面内判定
  function is_in_sight(jq_obj) {
    var scroll_top = $(window).scrollTop();
    var scroll_bottom = scroll_top + $(window).height();
    var target_top = jq_obj.offset().top;
    var target_bottom = target_top + jq_obj.height();
    if (scroll_bottom > target_top && scroll_top < target_bottom) {
      return true;
    } else {
      return false;
    }
  }
});
