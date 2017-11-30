Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 0) {
                    $('#navbar, section.sidemenu').addClass('minimized');
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК</p>');
                } else {
                    $('#navbar, section.sidemenu').removeClass("minimized");
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК</p>');
                }
            });

            if ($('.device-mobile').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК</p>');
            } else if ($('.device-tablet').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ООО "ЛУБРИТЭК"</p>');
            } else if ($('.device-normal').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ЛУБРИТЭК"</p>');
            } else {
                $('#block-sitename div.field--name-body').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ЛУБРИТЭК" WIDE</p>');
            }

            $(window).off('resize').on('resize', function () {
                if ($('.device-mobile').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК</p>');
                } else if ($('.device-tablet').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ООО "ЛУБРИТЭК"</p>');
                } else if ($('.device-normal').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ЛУБРИТЭК"</p>');
                } else {
                    $('#block-sitename div.field--name-body').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "ЛУБРИТЭК" WIDE</p>');
                }
            });

          $('.arrow-down').off('click').on('click', function () {
            $('.arrow-down').toggleClass('moving');
          });
            /*MOBILE landscape height FIX*/
          $( window ).on( "orientationchange", function( event ) {
            $(document).css({ 'height' : $(window).height() });
            $(document).css({ 'width' : $(window).width() });
          });

          $('.region-sidemenu h2').off('click.tm').on('click.tm', function(event){
              $('.region-sidemenu ul.menu:visible').hide('slow');
              $(event.target).next('ul.menu:hidden').show('slow');
          });

          $('.side-menu-burger-link').off('click.burger').on('click.burger', function(){
              $('section.sidemenu, .region-sidemenu').toggleClass('active');
              $('.side-menu-burger--icon').toggleClass('crossed');
          });



             /*$('#navbar').off("mouseenter.navbar").on( "mouseenter.navbar", function () {
                 if ($('#navbar').hasClass("minimized")){
                     $('#navbar').removeClass("minimized");
                   $('#block-sitename div').html('<p>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ</br>"ЛУБРИТЭК"</p>');
                 }
             } );
             $('#navbar').off("mouseleave.navbar").on("mouseleave.navbar", function () {
               if ($(window).scrollTop() > 0){
                 $('#navbar').addClass("minimized");
                 $('#block-sitename div').html('<p>ЛУБРИТЭК</p>');
               }
             } );*/

        })(jQuery);
    }
};