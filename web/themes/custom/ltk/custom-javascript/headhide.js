Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 0) {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap').addClass('minimized');
                    $('body').addClass('body-minimized');
                } else {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap').removeClass("minimized");
                    $('body').removeClass('body-minimized');
                }
            });

            /*Первоначальное положение, до ресайза*/
            if ($('.device-mobile').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК MOB</p>');
            } else if ($('.device-tablet').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК TAB</p>');
            } else if ($('.device-normal').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК NOR</p>');
            } else {
                $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК W</p>');
            }

            /*Событие на рейсайз*/
            $(window).off('resize').on('resize', function () {
                if ($('.device-mobile').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК MOB</p>');
                } else if ($('.device-tablet').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК TAB</p>');
                } else if ($('.device-normal').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК NOR</p>');
                } else {
                    $('#block-sitename div.field--name-body').html('<p>ЛУБРИТЭК W</p>');
                }
            });

            /*MOBILE landscape height FIX*/
          $( window ).on( "orientationchange", function( event ) {
            $(document).css({ 'height' : $(window).height() });
            $(document).css({ 'width' : $(window).width() });
          });
/*Анимация категорий в меню*/
          $('.region-sidemenu h2').off('click.tm').on('click.tm', function(event){
            $('.region-sidemenu ul.menu:visible').hide('slow');
            $(event.target).siblings('ul.menu:hidden').show('slow');
          });
/*Анимация выдвигающегося бокового меню*/
          $('.side-menu-burger-link').off('click.burger').on('click.burger', function(){
              $('.region-sidemenu').toggleClass('active'); //Выезжает меню
              $('.side-menu-burger--icon').toggleClass('crossed'); //Бургер становится крестиком
          });
        })(jQuery);
    }
};