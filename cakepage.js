var element_pickupdate = document.getElementById('cf7-pickup-date'); //希望日フォーム要素
element_pickupdate.setAttribute('readonly', true); //手動入力不可

var element_pickuptime = document.getElementById('cf7-timepicker'); //希望時間フォーム要素
element_pickuptime.setAttribute('readonly', true); //手動入力不可

jQuery(function($){
  $('input[name=email_confirm]').on('copy cut paste', function(e){
    e.preventDefault();
  });
});
