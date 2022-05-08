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
	/*jquery datepicker関係*/
	wp_enqueue_style( 'jquery-ui_style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', [] );
	wp_enqueue_script('jquery3.6.0', '//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', []);
	wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', []);
	wp_enqueue_script('jquery-ui-datepicker', '//ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js', []);
	/*jquery datepicker関係*/

	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/myscript.js' ) );
	wp_enqueue_script('myjs', get_stylesheet_directory_uri() . '/myscript.js', [], $timestam );

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
