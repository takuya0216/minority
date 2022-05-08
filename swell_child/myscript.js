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

  /* window scroll function start*/
  $(window).scroll(function () {
    scroll_effect();
  }).trigger('scoll');
  /* window scroll function end*/

  /*トップのスクロールボタン矢印*/
  $('p-mainVisual__scrollArrow').empty();
  $('.p-mainVisual__scroll').html('<svg class="p-mainVisual__scrollArrow" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 34" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><line x1="11" y1="0" x2="11" y2="29"></line></svg><span class="p-mainVisual__scrollLabel">Scroll</span>');

	/*トップへ戻るボタン スクロール*/
  $('#pagetop').on('click',function(){
      $("html").animate({scrollTop: 0}, { duration: scrollTime, easing: 'swing', });
  });

  /*日付カレンダー設定*/
  $("#cf7-pickup-date").datepicker({
    dateFormat: 'yy年mm月dd日',
	  yearSuffix: '年',
	  showMonthAfterYear: true,
	  monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
	  dayNames: ['日', '月', '火', '水', '木', '金', '土'],
	  dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
	  minDate: "+5d",
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
        if (elemPos < scroll + windowHeight){
            $(this).addClass('effect-scroll');
        }
    });
  }
});
