$(function () {

    //CART

    function showCart(cart) {
        $('#cart-modal .modal-cart-content').html(cart);
        const myModalEl = document.querySelector('#cart-modal');
        const modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
        modal.show();
        // Если '.cart-qty' есть текст, мо ты его добавим в count-items.
        if ($('.cart-qty').text()) {
            $('.count-items').text($('.cart-qty').text());
        } else {
            $('.count-items').text('0');
        }
    }

    $('#get-cart').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'cart/show',
            type: 'GET',
            success: function (res) {
                showCart(res);
            },
            error: function () {
                alert('Error!');
            }
        });
    });

    $('#cart-modal .modal-cart-content').on('click', '.del-item', function (e) {
        e.preventDefault();
        // id товара
        const id = $(this).data('id');
        $.ajax({
            url: 'cart/delete',
            type: 'GET',
            data: {id: id},
            success: function (res) {
                // нам нужно понять где мы находимся
                const url = window.location.toString();
                // если на странице cart/view
                if (url.indexOf('cart/view') !== -1) {
                    // перезапрашиваем страницу (перезагружаем)
                    window.location = url;
                } else {
                    showCart(res);
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    })
    ;

    $('#cart-modal .modal-cart-content').on('click', '#clear-cart', function () {
        $.ajax({
            url: 'cart/clear',
            type: 'GET',
            success: function (res) {
                showCart(res);
            },
            error: function () {
                alert('Error!');
            }
        });
    });

    $('.add-to-cart').on('click', function (e) {
        e.preventDefault(); // отменяем переход по ссылке
        const id = $(this).data('id');
        const qty = $('#input-quantity').val() ? $('#input-quantity').val() : 1;
        const $this = $(this); // объект по которому произошел клик
        // console.log(id, qty); // id товара и количество

        // отправка AJAX запроса
        $.ajax({
            // запрос на контроллер cart и action add
            url: 'cart/add',
            type: 'GET',
            data: {id: id, qty: qty},
            success: function (res) {
                console.log("res: ", res)
                showCart(res);
                $this.find('i').removeClass('fa-shopping-cart').addClass('fa-luggage-cart');

            },
            error: function (res) {
                console.log('Error in AJAX')
            },
        })

    });

    // SORT
    $('#input-sort').on('change', function () {
        window.location = PATH + window.location.pathname + '?' + $(this).val();
    });

    $('.open-search').click(function (e) {
        e.preventDefault();
        $('#search').addClass('active');
    });
    $('.close-search').click(function () {
        $('#search').removeClass('active');
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('#top').fadeIn();
        } else {
            $('#top').fadeOut();
        }
    });

    $('#top').click(function () {
        $('body, html').animate({scrollTop: 0}, 700);
    });

    $('.sidebar-toggler .btn').click(function () {
        $('.sidebar-toggle').slideToggle();
    });

    $('.thumbnails').magnificPopup({
        type: 'image',
        delegate: 'a',
        gallery: {
            enabled: true
        },
        removalDelay: 500,
        callbacks: {
            beforeOpen: function () {
                this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
                this.st.mainClass = this.st.el.attr('data-effect');
            }
        }
    });

    // $('dropdown-item').on('click', function () {
    $('#languages button').on('click', function () {
        const lang_code = $(this).data('langcode');
        window.location = PATH + '/language/change?lang=' + lang_code;
    });


    // wishlist
    // деллигируем событие на .add-to-wishlist
    $('.product-card').on('click', '.add-to-wishlist', function (e) {
        e.preventDefault();
        const id = $(this).data('id'); // id товара
        const $this = $(this);
        $.ajax({
            // данные уходят на этот адресс
            url: 'wishlist/add',
            type: 'GET',
            data: {id: id},
            success: function (res) {
                res = JSON.parse(res);
                // console.log(res);
                Swal.fire(
                    res.text,
                    '',
                    res.result
                );

                if (res.result == 'success') {
                    $this.removeClass('add-to-wishlist').addClass('delete-from-wishlist');
                    $this.find('i').removeClass('far fa-heart').addClass('fas fa-hand-holding-heart');
                }

            },
            error: function () {
                alert('Error!');
            }
        });
    });

    $('.product-card').on('click', '.delete-from-wishlist', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const $this = $(this);
        $.ajax({
            url: 'wishlist/delete',
            type: 'GET',
            data: {id: id},
            success: function (res) {
                // нам нужно понять где мы находимся
                const url = window.location.toString();
                // если на странице избранного
                if (url.indexOf('wishlist') !== -1) {
                    // перезапрашиваем страницу (перезагружаем)
                    window.location = url;
                } else {
                    // на странице категорий или главной
                    res = JSON.parse(res);
                    Swal.fire(
                        res.text,
                        '',
                        res.result
                    );
                    if (res.result == 'success') {
                        $this.removeClass('delete-from-wishlist').addClass('add-to-wishlist');
                        $this.find('i').removeClass('fas fa-hand-holding-heart').addClass('far fa-heart');
                    }
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    });

});