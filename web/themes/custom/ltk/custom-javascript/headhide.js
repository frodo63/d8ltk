Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 0) {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap, #edit-actions--2, .region-highlighted').addClass('minimized');
                    $('body').addClass('body-minimized');
                } else {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap, #edit-actions--2, .region-highlighted').removeClass("minimized");
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
            $('.region-highlighted').toggleClass('active');//Сжимается строка хлебных крошек
            $('.region-sidemenu').toggleClass('active'); //Выезжает меню
            $('.side-menu-burger--icon').toggleClass('crossed'); //Бургер становится крестиком
          });

          /*Анимация cfp-3 категория и содержимое отрасли*/
          $('.cfp3-title').off('mouseover.cpf3').on('mouseover.cfp3', function (event) {
            $('.cfp3-popup').hide();
            var count = $(event.target).attr('data-count');
            var target = $('.cfp3-popup[data-count='+count+']');
            if (target.css('display') == 'none') {
              target.show();
            }
            else {target.hide()};
          });

          /*Анимация cfp-2 категория и содержимое отрасли*/
          $('.special').off('mouseover.cpf2').on('mouseover.cfp2', function (event) {
              $('div[class^="cfp2-"]').hide();
              var tag = $(event.target).attr('data-tag');
              var target = $('.cfp2-'+tag);
              if (target.css('display') == 'none') {target.show()}
              else {target.hide()};
              target = null;
          });

          /*Анимация cfp2-divы ховеры для всех картинок*/
          $('div[class^="cfp2-"] li').off('mouseover.cfp2li').on('mouseover.cfp2li' , function () {
            var count = $(event.target).attr('data-count');
            var path ="sites/default/files/theme-images/spes/"+count+".png";
            var target = $(event.target).parents('div[class^="cfp2-"]');
            target.css('background-image','url('+path+')');
          })

        })(jQuery);
    }
};