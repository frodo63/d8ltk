Drupal.behaviors.basic = {
    attach: function (context, settings) {
        (function ($) {
            $(window).scroll(function () {
                var scroll = $(window).scrollTop();

                if (scroll > 50) {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap, #edit-actions--2, .region-highlighted, .region-add-request, .request-button').addClass('minimized');
                    $('body').addClass('body-minimized');
                } else {
                    $('#navbar, section.sidemenu, .region-sidemenu, .side-menu--wrap, #edit-actions--2, .region-highlighted, .region-add-request, .request-button').removeClass("minimized");
                    $('body').removeClass('body-minimized');
                }
            });

            /*Первоначальное положение, до ресайза*/
            if ($('.device-mobile').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК</h1>');
            } else if ($('.device-tablet').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК M</h1>');
            } else if ($('.device-normal').is(":visible")) {
                $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК L</h1>');
            } else {
                $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК XL</h1>');
            }

            /*Событие на рейсайз*/
            $(window).off('resize').on('resize', function () {
                if ($('.device-mobile').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК</h1>');
                } else if ($('.device-tablet').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК M</h1>');
                } else if ($('.device-normal').is(":visible")) {
                    $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК L</h1>');
                } else {
                    $('#block-sitename div.field--name-body').html('<h1>ЛУБРИТЭК XL</h1>');
                }
            });

            /*MOBILE landscape height FIX*/
          $( window ).on( "orientationchange", function( event ) {
            $(document).css({ 'height' : $(window).height() });
            $(document).css({ 'width' : $(window).width() });
          });

          /*Анимация выдвигающегося бокового меню*/
          $('.side-menu-burger-link').off('click.burger').on('click.burger', function(){
            $('.region-highlighted').toggleClass('active');//Сжимается строка хлебных крошек
            $('.region-sidemenu').toggleClass('active'); //Выезжает меню
            $('.side-menu-burger--icon').toggleClass('crossed'); //Бургер становится крестиком
          });

            /*Анимация категорий в меню*/
            $('.region-sidemenu h2').off('click.tm').on('click.tm', function(event){
                //$('.region-sidemenu ul.menu:visible').hide('slow');//Закрыть все менюшки
                $(event.target).siblings('ul.menu').toggle('slow');//ОТкрыть эту одну
                $(event.target).children('span.glyphicon').toggleClass('glyphicon-triangle-bottom glyphicon-triangle-top');
            });

          /*Анимация спускающейся формы заявки*/
          $('.request-button').off('click.request').on('click.request', function(){
            $('.request-button span').toggleClass('arr-up arr-down');
            $('.request-button').toggleClass('button-down');
            $('.order-btn').toggle();
            $('.region-add-request').toggleClass('down');
            if($('.region-add-request').hasClass('down')){
              $('.request-button p').text('СКРЫТЬ ЗАЯВКУ');
            }else{
              $('.request-button p').text('ЗАКАЗАТЬ');
            };
          });

            $('.order-btn').off('click.orderbtn').on('click.orderbtn', function(){
                $('.request-button span').toggleClass('arr-up arr-down');
                $('.request-button').toggleClass('button-down');
                $('.order-btn').toggle();
                $('.region-add-request').toggleClass('down');
                if($('.region-add-request').hasClass('down')){
                    $('.request-button p').text('СКРЫТЬ ЗАЯВКУ');
                }else{
                    $('.request-button p').text('ЗАКАЗАТЬ');
                };
            });
          /*Анимация стрелочек на кнопке вызова формы выше*/



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
            var count = $(this).attr('data-count');
            var path ="sites/default/files/theme-images/spes/"+count+".png";
            var target = $(this).parents('div[class^="cfp2-"]');
            target.css('background-image','url('+path+')');
          })

          /*Меняем value с And на "До" во втором инпуте Выбора Вязкости масла*/
          $('.form-item label[for="edit-field-oil-visc-value-max"]').html("до:");

          /*Скролл-линки на главной*/
          //$('.menu.navbar-nav li a').off('click.scrlnk').on('click.scrlnk', function (event) {
            /*event.preventDefault();*/
          //  var anchor = $(event.target).attr('href').substr(1);
           // $('html, body').animate({scrollTop: $('#'+anchor).offset().top-100}, 1000);
          //})
          /**/

        })(jQuery);
    }
};