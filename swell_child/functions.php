<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */


/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);


/**
 * 子テーマでのファイルの読み込み
 */
add_action('wp_enqueue_scripts', function() {

	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'child_style', get_stylesheet_directory_uri() .'/style.css', [], $timestamp );

	/* その他の読み込みファイルはこの下に記述 */
	/*jquery datepicker*/
	wp_enqueue_style( 'jquery-ui_style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', [] );
	wp_enqueue_script('jquery3.6.0', '//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', []);
	wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', []);
	wp_enqueue_script('jquery-ui-datepicker', '//ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js', []);
	/*jquery datepicker*/
	/*jquery timepicker*/
	wp_enqueue_style( 'jquery-timepicker-style', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css', [] );
	wp_enqueue_script('jquery-timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', [],true);
	/*jquery timepicker*/

	/*SweetAlert*/
	wp_enqueue_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@11', []);

	/*カスタマイズ用javaスクリプト*/
	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/myscript.js' ) );
	wp_enqueue_script('myjs', get_stylesheet_directory_uri() . '/myscript.js', [], $timestamp,true );

}, 11);

function child_style_both(){

  $timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style_both.css' ) );
  //フロントとエディタの両方に適応するCSS
  wp_enqueue_style('child-style-both', get_stylesheet_directory_uri() . '/style_both.css', [], $timestamp);
  //AdobeFonts
  wp_enqueue_style('mytheme-adobefonts', 'https://use.typekit.net/wax1taj.css', array(), null);
  wp_enqueue_style('mytheme-googlefonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap', [], null);
}
add_action('enqueue_block_assets', 'child_style_both');

/*ショートコード*/
/*ショートコードを使ったphpファイルの呼び出し方法*/
function Include_my_php($params = array()) {
    extract(shortcode_atts(array(
        'file' => 'default'
    ), $params));
    ob_start();
    include(get_stylesheet_directory() . "/$file.php");
    return ob_get_clean();
}
add_shortcode('include_myphp', 'Include_my_php');

//MTS Simple Bokking
//予約フォームの人数種別ラベル（大人・小人）を消す
function my_booking_form_count_label() {
    return '';
}
add_filter('booking_form_count_label', 'my_booking_form_count_label');

//予約可能マーク表示制御
//カレンダー＝マーク表示、予約時間割り＝残席数表示
function mts_daily_mark($mark, $number) {
	$output = "残り" . $number . "席";
	return $output;
}
add_filter('mtssb_daily_mark', 'mts_daily_mark', 10, 2);
