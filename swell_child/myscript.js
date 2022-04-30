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
  /*ヘッダーをスクロールで縮める必要あれば使う
  var header_menubtn = $('.l-header__menuBtn');
  var header_icon_border = $('.icon-menu-thin');
  var header = $('.l-header');
  */
  const scrollTime = 700;

  /* window scroll function start*/
  $(window).scroll(function () {
    /*ヘッダーをスクロールで縮める必要あれば使う
    if ($(this).scrollTop() > 100) {
      header_icon_border.addClass('scroll');
      header_menubtn.addClass('scroll');
	    header.addClass('scroll');
    } else {
      header_icon_border.removeClass('scroll');
      header_menubtn.removeClass('scroll');
	    header.removeClass('scroll');
    }
    */
  });
  /* window scroll function end*/

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

});
