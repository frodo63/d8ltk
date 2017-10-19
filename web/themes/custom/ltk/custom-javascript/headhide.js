Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 0) {
                    $("#navbar").addClass('minimized');
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК</p>');
                } else {
                    $("#navbar").removeClass("minimized");
                    $('#block-sitename div.field--name-body').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ</br>"ЛУБРИТЭК"</p>');
                }
            });
            // $('#navbar').off("mouseenter.navbar").on( "mouseenter.navbar", function () {
            //     if ($('#navbar').hasClass("minimized")){
            //         $('#navbar').removeClass("minimized");
            //       $('#block-sitename div').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ</br>"ЛУБРИТЭК"</p>');
            //     }
            // } );
            // $('#navbar').off("mouseleave.navbar").on("mouseleave.navbar", function () {
            //   if ($(window).scrollTop() > 0){
            //     $('#navbar').addClass("minimized");
            //     $('#block-sitename div').html('<p>ЛУБРИТЭК</p>');
            //   }
            // } );

        })(jQuery);
    }
};