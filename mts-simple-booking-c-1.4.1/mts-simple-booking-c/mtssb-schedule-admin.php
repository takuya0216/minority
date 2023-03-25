<?php
/**
 * MTS Simple Booking Articles スケジュール管理モジュール
 *
 * @Filename	mtssb-schedule-admin.php
 * @Date		2012-04-27
 * @Author		S.Hayashi
 *
 * Updated to 1.4.0 on 2018-01-29
 * Updated to 1.2.0 on 2012-12-22
 */

class MTSSB_Schedule_Admin { //extends MTSSB_Schedule {
	const VERSION = '1.4.0';
	const PAGE_NAME = 'simple-booking-schedule';

	private static $iSchedule = null;

	private $domain;

	// WPオプションス ケジュールデータ
	private $schedule = null;

	// 読み込んだ予約品目データ
	private $article_id;
	private $theyear;
	private $articles = null;

	// 操作対象データ
	private $themonth = 0;		// 当該カレンダーのunix time

	private $action = '';
	private $message = '';
	private $errflg = false;

	/**
	 * インスタンス化
	 *
	 */
	public static function get_instance() {

		if (!isset(self::$iSchedule)) {
			self::$iSchedule = new MTSSB_Schedule_Admin();
		}

		return self::$iSchedule;
	}

	public function __construct() {
		global $mts_simple_booking;

		//parent::__construct();
		$this->domain = MTS_Simple_Booking::DOMAIN;

		// CSSロード
		$mts_simple_booking->enqueue_style();

		// Javascriptロード
		wp_enqueue_script("mtssb_schedule_admin_js", plugins_url("js/mtssb-schedule-admin.js", __FILE__), array('jquery'));

	}

	/**
	 * 管理画面メニュー処理
	 *
	 */
	public function schedule_page() {

		$this->errflg = false;
		$this->message = '';

		// 予約品目の読み込み
		$this->articles = MTSSB_Article::get_all_articles();
		if (empty($this->articles)) {
			$this->message = __('The exhibited reservation item data has nothing.', $this->domain);
		}
		$this->article_id = key($this->articles);

		$this->themonth = mktime(0, 0, 0, date_i18n('n'), 1, date_i18n('Y'));

		if (isset($_REQUEST['action'])) {

			//edit
			//タイマーモードの設定
			if (isset($_POST['time_save'])){
				if($_POST['time_save'] == 'true'){
					$updatetime = $_POST['reserve_time'];
					update_option('mtssb_reserve_time', $updatetime);
					$_REQUEST['action'] = 'time_save';
				}
			}
			//タイマーモードのリセット
			if (isset($_POST['reset_reserve_time'])){
				$_REQUEST['action'] = 'reset_reserve_time';
			}

			//edit
			//タイマーモードの実行追加
			//
			switch ($_REQUEST['action']) {
				case 'schedule' :
					$this->_schedule_parameter(intval($_GET['article_id']), intval($_GET['schedule_year']), intval($_GET['schedule_month']));
					break;
				case 'save' :
					if (wp_verify_nonce($_POST['nonce'], self::PAGE_NAME . '-save')) {
						$this->article_id = intval($_POST['article_id']);
						$this->_schedule_update();
						$this->_schedule_parameter(intval($_POST['article_id']), intval($_POST['schedule_year']), intval($_POST['schedule_month']));
						$this->message = __('Schedule has been saved.', $this->domain);
					} else {
						$this->errflg = true;
						$this->message = "Nonce error";
					}
					break;
				case 'time_save' :
					$this->_schedule_parameter(intval($_POST['article_id']), intval($_POST['schedule_year']), intval($_POST['schedule_month']));
					if ( !wp_verify_nonce($_POST['nonce'], self::PAGE_NAME . '-save') ) {
						$this->errflg = true;
						$this->message = "Nonce error";
						break;
					}
					//品目・年・月の組み合わせをキーとして、キーに対し一つしかスケジュールはできない。
					if ( wp_next_scheduled( 'mtsbb_reserve_time_schedule' , array( $_POST['article_id'], $_POST['schedule_year'], $_POST['schedule_month']) )) {
						$this->errflg = true;
						$this->message = __('既にスケジュールが予約されています。', $this->domain);
						break;
					}
					$this->article_id = intval($_POST['article_id']);
					//更新をスケジュールする
					if( $this->_save_next_schedule( $_POST['article_id'], $_POST['schedule_year'], $_POST['schedule_month'], $_POST['schedule'], $updatetime )){
						$this->_schedule_parameter(intval($_POST['article_id']), intval($_POST['schedule_year']), intval($_POST['schedule_month']));
						$this->message = __('更新予約が設定されました。', $this->domain);
						$this->errflg = false;
					}
					else{
						if(!$this->errflg){
							$this->errflg = true;
							$this->message = __('更新予約に失敗。', $this->domain);
						}
					}
					break;
				case 'reset_reserve_time':
					$this->_schedule_parameter(intval($_POST['article_id']), intval($_POST['schedule_year']), intval($_POST['schedule_month']));
					if($this->reset_reserve_time( $_POST['article_id'], $_POST['schedule_year'], $_POST['schedule_month'])){
						$this->message = __('更新予約がキャンセルされました。', $this->domain);
					}
					else{
						if(!$this->errflg){
							$this->errflg = true;
							$this->message = __('更新予約キャンセルに失敗。', $this->domain);
						}
					}
					break;
				default:
					$this->errflg = true;
					$this->message  = "Unknown action";
					break;
			}
		}
		//edit
		// 対象年月のスケジュールデータの読み込み
		if(wp_next_scheduled('mtsbb_reserve_time_schedule', array("$this->article_id", strval(date('Y', $this->themonth)), strval(date('n', $this->themonth))))){
			$key_name = 'next_' . MTS_Simple_Booking::SCHEDULE_NAME . date_i18n('Ym', $this->themonth);
			$this->schedule = get_post_meta($this->article_id, $key_name, true);
		}
		else{
			$key_name = MTS_Simple_Booking::SCHEDULE_NAME . date_i18n('Ym', $this->themonth);
			$this->schedule = get_post_meta($this->article_id, $key_name, true);
		}
		//edit

?>
	<div class="wrap">
		<h2><?php _e('Schadule Management', $this->domain); ?></h2>
		<?php if (!empty($this->message)) : ?>
			<div class="<?php echo ($this->errflg) ? 'error' : 'updated' ?>"><p><strong><?php echo $this->message; ?></strong></p></div>
		<?php endif; ?>

		<?php if (!empty($this->articles)) : ?>

			<?php $this->_select_form() ?>

			<?php $this->_schedule_form() ?>

		<?php endif; ?>

	</div><!-- wrap -->
<?php
		return;

	}

	/**
	 * 予約スケジュールのフォーム表示出力
	 */
	private function _schedule_form() {
		$weeks = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

		if (!empty($this->schedule)) {
			$schedule = $this->schedule;
		} else {
			$schedule = $this->_new_month($this->themonth);
		}

		// 過去スケジュールの場合はdisableセット
		$disabled = $this->themonth < mktime(0, 0, 0, date_i18n('n'), 1, date_i18n('Y')) ? ' disabled="disabled"' : '';

		// カレンダー生成パラメータ
		$starti = date('w', $this->themonth);
		$endd = count($schedule);
		$endi = $starti + $endd + 5 - date('w', mktime(0, 0, 0, date('n', $this->themonth), $endd, date('Y', $this->themonth)));

?>
	<form method="post" action="?page=<?php echo self::PAGE_NAME ?>">
		<div class="mtssb-schedule">
			<?php foreach ($weeks as $wname) {
				$week = strtolower($wname);
				echo "<div class=\"schedule-box column-title $week\"><label>" . __($wname)
				 . "<input id=\"schedule-check-$week\" class=\"$week\" type=\"checkbox\"$disabled /></label></div>";
			} ?>

			<?php
				for ($i = 0, $day = 1 - $starti; $i <= $endi ; $i++, $day++) {
					// フロートキャンセル
					if ($i % 7 == 0) {
						echo "<div class=\"clear\"> </div>\n";
					}

					if (0 < $day && $day <= $endd) {
						$week = strtolower($weeks[$i % 7]);
						$day = sprintf("%02d", $day);
						echo "<div class=\"schedule-box $week\">";
						echo "<div class=\"schedule-day $week\"><label for=\"schedule-open-$day\">$day</label></div>";
						echo "<div class=\"schedule-open" . ($schedule[$day]['open'] ? ' open' : '') . "\"><input type=\"hidden\" name=\"schedule[$day][open]\" value=\"0\"$disabled />";
						echo "<input id=\"schedule-open-$day\" class=\"$week\" type=\"checkbox\" name=\"schedule[$day][open]\" value=\"1\"" . (0 < $schedule[$day]['open'] ? ' checked="checked"' : '') . " $disabled /></div>";
						echo "<div class=\"schedule-delta\"><input type=\"text\" name=\"schedule[$day][delta]\" value=\"" . (isset($schedule[$day]['delta']) ? $schedule[$day]['delta'] : 0) . "\"$disabled /></div>";
						echo "<div class=\"schedule-class\"><input type=\"text\" name=\"schedule[$day][class]\" value=\"" . $schedule[$day]['class'] . "\"$disabled /></div>";
					} else {
						echo '<div class="schedule-box no-day"> ';
					}
					echo "</div>\n";
				}
			?>
			<div class="clear"> </div>
		</div><!-- mtssb-schedule -->

		<!-- edit -->
		<?php $NEXT_SCHEDULE = wp_next_scheduled('mtsbb_reserve_time_schedule', array("$this->article_id", strval(date('Y', $this->themonth)), strval(date('n', $this->themonth)))); ?>
		<?php if (!$disabled) : ?><div class="schedule-footer">
			<input type="hidden" name="article_id" value="<?php echo $this->article_id ?>" />
			<input type="hidden" name="schedule_year" value="<?php echo date('Y', $this->themonth) ?>" />
			<input type="hidden" name="schedule_month" value="<?php echo date('n', $this->themonth) ?>" />
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(self::PAGE_NAME . '-save') ?>" />
			<input type="hidden" name="action" value="save" />
			<input type="submit" class="button-primary" value="<?php _e('Save Schedule', $this->domain) ?>" id="schedule-save" <?php if($NEXT_SCHEDULE){ echo "disabled"; } ?> />
		</div><?php endif; ?>
		<?php if (!$disabled) : ?><div class="schedule-timer-option">
		    <input type="checkbox" name="time_save" value="true" /><span>更新予約する</span>
		    <input type="datetime-local" name="reserve_time" value="<?php echo esc_attr( get_option( 'mtssb_reserve_time' ) ); ?>" style="
    margin-left: 1em;" />
		    <input type="submit" class="button-primary" value="更新予約をキャンセル" name="reset_reserve_time" <?php if(!$NEXT_SCHEDULE){ echo "disabled"; } ?> />
		    <p>　次の更新時刻：<?php

		            if($NEXT_SCHEDULE){
						echo date("Y-m-d H:i:s", $NEXT_SCHEDULE) . '　' . $this->get_time_left($NEXT_SCHEDULE);
					}
					else{
						echo '更新予約はありません。';
					}
			?>
		    </p>
		<p class="description">　・「更新予約する」がチェックされた状態で「スケジュールを保存」を押すと、設定時刻で更新予約されます。</p>
		<p class="description">　・各スケジュールページ毎に最大一つまで予約できます。</p>
		<p class="description">　・更新予約中は、手動予約はできません。「更新予約をキャンセル」ボタンを押して予約をキャンセルしてください。</p>
		<p class="description">　・更新予約中は、管理画面には更新予定のスケジュールが表示されます。予約をキャンセルすると、元のスケジュールが表示されます。</p>
		</div><?php endif; ?>
		<!-- edit -->
	</form>

	<div id="schedule-description">
		<p><?php _e('Example:', $this->domain) ?></p>
		<div class="schedule-box">
			<div class="schedule-day"><?php _e('Day', $this->domain) ?></div>
			<div class="schedule-open_"><?php _e('Schedule Open', $this->domain) ?></div>
			<div class="schedule-delta"><?php _e('Delta', $this->domain) ?></div>
			<div class="schedule-class"><?php _e('Class Name', $this->domain) ?></div>
		</div>
	</div>

<?php
	}

	/**
	 * 予約品目、スケジュール年の選択フォーム出力
	 */
	private function _select_form() {
		$past = 1;
		$future = 1;

		$theyear = date('Y', $this->themonth);
		$themonth = date('n', $this->themonth);

		// リンク
		$this_year = date_i18n('Y');
		$min_month = mktime(0, 0, 0, 1, 1, $this_year - $past);
		$max_month = mktime(0, 0, 0, 12, 1, $this_year + $future);

		$prev_month = mktime(0, 0, 0, $themonth - 1, 1, $theyear);
		$prev_str = date('Y-m', $prev_month);
		$next_month = mktime(0, 0, 0, $themonth + 1, 1, $theyear);
		$next_str = date('Y-m', $next_month);

?>
	<div id="schedule-select-article">
		<h3><?php _e('Reservation item and the year', $this->domain) ?></h3>
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo self::PAGE_NAME ?>" />
			<select class="select-article" name="article_id">
				<?php foreach ($this->articles as $article_id => $article) {
					echo "<option value=\"$article_id\"";
					if ($article_id == $this->article_id) {
						echo ' selected="selected"';
					}
					echo ">{$this->articles[$article_id]['name']}</option>\n";
				} ?>
			</select>

			<?php _e('Year: ', $this->domain); ?>
			<select class="select-year" name="schedule_year">
				<?php for ($y = $this_year - $past; $y <= $this_year + $future; $y++) {
					echo "<option value=\"$y\"";
					if ($y == $theyear) {
						echo ' selected="selected"';
					}
					echo ">$y</option>\n";
				} ?>
			</select>

			<?php _e('Month:',$this->domain); ?>
			<select class="select-month" name="schedule_month">
				<?php for ($m = 1; $m <= 12; $m++) {
					echo "<option value=\"$m\"";
					if ($m == $themonth) {
						echo ' selected="selected"';
					}
					echo ">$m</option>\n";
				} ?>
			</select>

			<input class="button-secondary" type="submit" value="<?php _e('Change') ?>" />
			<input type="hidden" name="action" value="schedule" />
		</form>

		<h3><?php echo date('Y-m ', $this->themonth) . $this->articles[$this->article_id]['name'] ?></h3>
		<ul class="subsubsub">
			<li><?php
				if ($min_month <= $prev_month) {
					echo '<a href="?page=' . self::PAGE_NAME . "&article_id={$this->article_id}&schedule_year="
					 . date('Y', $prev_month) . "&schedule_month=" . date('n', $prev_month) . "&action=schedule\">$prev_str</a>";
				} else {
					echo $prev_str;
				} ?> | </li>
			<li><?php
				if ($next_month <= $max_month) {
					echo '<a href="?page=' . self::PAGE_NAME . "&article_id={$this->article_id}&schedule_year="
					 . date('Y', $next_month) . "&schedule_month=" . date('n', $next_month) . "&action=schedule\">$next_str</a>";
				} else {
					echo $next_str;
				} ?></li>
		</ul>
		<div class="clear"> </div>
	</div>

<?php
	}

	/**
	 * スケジュールデータの保存
	 *
	 */
	private function _schedule_update() {
		$article_id = intval($_POST['article_id']);
		if (!isset($this->articles[$article_id])) {
			return;
		}

		$key_name = MTS_Simple_Booking::SCHEDULE_NAME . sprintf("%04d%02d", intval($_POST['schedule_year']), intval($_POST['schedule_month']));
		update_post_meta($article_id, $key_name, $_POST['schedule']);

	}

	//edit
	/**
	 * 次回のスケジュールイベント登録
	 * 品目、年、月をキーとしたスケジュールのデータを一時保存
	 * $kye_nameに一時保存用を指定
	 */
	private function _save_next_schedule($ARTICLE_ID, $YEAR, $MONTH, $SCHEDULE, $UPDATETIME){

		//date_default_timezone_set( wp_timezone()->getName() );
		$target_time = strtotime($UPDATETIME); //指定時刻を Unix タイムスタンプに変換
		$deltatime = $target_time - strtotime(current_time("Y-m-d H:i"));
		if($deltatime <= 0){
			$this->errflg = true;
			$this->message = __('未来の時間を設定してください。', $this->domain);
			return false;
		}

		if(wp_schedule_single_event( time() + $deltatime, 'mtsbb_reserve_time_schedule', array( $ARTICLE_ID, $YEAR , $MONTH))){
			//次のスケジュールを保存
			$key_name = 'next_' . MTS_Simple_Booking::SCHEDULE_NAME . sprintf("%04d%02d", intval($YEAR), intval($MONTH));
			update_post_meta(intval($ARTICLE_ID), $key_name, $SCHEDULE);
			return true;
		}
		return false;
	}

	//edit
	/**
	 * 予約スケジュールの削除
	 */
	private function reset_reserve_time($ARTICLE_ID, $YEAR, $MONTH){
		//一時保存スケジュールを取得
		$timestamp = wp_next_scheduled( 'mtsbb_reserve_time_schedule', array( $ARTICLE_ID, $YEAR , $MONTH) );
		if ( !$timestamp ) {
			$this->errflg = true;
			$this->message = __('更新予約が無いためキャンセルできませんでした。', $this->domain);
			return false;
		}
		return wp_unschedule_event( $timestamp, 'mtsbb_reserve_time_schedule', array( $ARTICLE_ID, $YEAR , $MONTH));
	}

	//edit
	/**
	 * スケジュールデータの保存
	 * 非同期処理のコールバック
	 * _save_next_scheduleで保存したスケジュールを取ってくる
	 */
	public function _schedule_update_async($ARTICLE_ID, $YEAR, $MONTH) {

		$aritcle_id = intval($ARTICLE_ID);
		$year = intval($YEAR);
		$month = intval($MONTH);

		//一時保存スケジュールを取得
		$key_name = 'next_' . MTS_Simple_Booking::SCHEDULE_NAME . sprintf("%04d%02d", $year, $month);
		$SCHEDULE = get_post_meta($aritcle_id, $key_name, true);
		//本体スケジュールを設定
		$key_name = MTS_Simple_Booking::SCHEDULE_NAME . sprintf("%04d%02d", $year, $month);
		update_post_meta($aritcle_id, $key_name, $SCHEDULE);

		//メール通知
		/*
		$message = "スケジュールイベントが実行されました。" . $response;

		//送信先メールアドレス
		$to = get_option('admin_email');

		//メールの件名
		$subject = 'スケジュールイベントの実行確認';

		//メールの本文
		$body = $message . "。実行時刻は" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "です。";

		//メールのヘッダー
		$headers = array('Content-Type: text/html; charset=UTF-8');

		//メール送信
		wp_mail( $to, $subject, $body, $headers );
		*/
	}

	//edit

	/**
	 * スケジュール管理に必要なパラメータの設定
	 *
	 */
	private function _schedule_parameter($article_id, $year, $month) {
		$past = 1;
		$future = 1;

		// 予約品目のIDチェック
		if (!isset($this->articles[$article_id])) {
			return;
		}

		// スケジュールの期間チェック
		$themonth = mktime(0, 0, 0, $month, 1, $year);

		$this_year = date_i18n('Y');
		if ($themonth < mktime(0, 0, 0, 1, 1, $this_year - $past)
		 || mktime(0, 0, 0, 12, 1, $this_year + $future) < $themonth) {
			return;
		};

		// パラメータの設定
		$this->article_id = $article_id;
		$this->themonth = $themonth;
	}

	/**
	 * スケジュール最小データ
	 *
	 * @daytime		xx年xx月xx日のunix time
	 */
	protected function _new_day($daytime=0) {

		return array(date('d', $daytime) => array(
			'open' => 0,		// 0:閉店 1:開店
			'delta' => 0,		// 予約数量の増減
			'class' => '',		// class 表示データ
		));
	}

	/**
	 * 1ヶ月の空スケジュール取得
	 *
	 * @datetime	xx年xx月1日のunix time
	 */
	protected function _new_month($monthtime) {

		// 当月日データ構築
		$next_month = mktime(0, 0, 0, date('n', $monthtime) + 1, 1, date('Y', $monthtime));

		$month = array();
		for ($daytime = $monthtime; $daytime < $next_month; $daytime += 86400) {
			$month += $this->_new_day($daytime);
		}

		return $month;
	}

	//edit
	/**
	 * 非同期処理登録
	 *
	 */
	static public function set_ajax_hook() {
		add_action('mtsbb_reserve_time_schedule' , array('MTSSB_Schedule_Admin', '_schedule_update_async'), 10, 3);
	}

	/**
	 * 残り時間を文字列フォーマットして返す
	 */
	 private function get_time_left($futuretime){
		 date_default_timezone_set( wp_timezone()->getName() );
		 $diff = $futuretime - time();
		 if($diff < 0){
			 return "0秒";
		 }
		 $hours = floor($diff / 3600); // 時間差を計算
		 $minutes = floor(($diff % 3600) / 60); // 分差を計算
		 $seconds = $diff % 60; // 秒差を計算
		 $time_left = $hours . "時間" . $minutes . "分" . $seconds . "秒";

		 if($hours > 0){
			 return $hours . "時間" . $minutes . "分" . $seconds . "秒";
		 }
		 elseif($minutes > 0){
			 return $minutes . "分" . $seconds . "秒";
		 }
		 return $seconds . "秒";
	 }
}
