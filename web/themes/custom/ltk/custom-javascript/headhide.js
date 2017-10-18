Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 0) {
                    $("#navbar").addClass('minimized');
                    $('#block-sitename div').html('<p>ЛУБРИТЭК</p>');
                } else {
                    $("#navbar").removeClass("minimized");
                    $('#block-sitename div').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ</br>"ЛУБРИТЭК"</p>');
                }
            });
            /*$('#navbar').on( "mouseover", function () {
                if ($('#navbar').hasClass("minimized")){
                    $('#navbar').removeClass("minimized");
                  $('#block-sitename div').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ</br>"ЛУБРИТЭК"</p>');
                }
            } );
          $('#navbar').on( "mouseout", function () {
            if ($(window).scrollTop() > 0){
              $('#navbar').addClass("minimized");
              $('#block-sitename div').html('<p>ЛУБРИТЭК</p>');
            }
          } );*/

        })(jQuery);
    }
};