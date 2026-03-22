/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

$(function() {

    // タブレットデザインが適応される画面最小幅
    // スタイルシート側を変更した場合、この値も同じ値にしてください
    var media_tablet_width = 768;

    $('.ec-blockTopBtn_media_pc').hide();

    $(window).on('scroll', function() {
        // ページトップフェードイン
        if ($(this).scrollTop() > 300) {
            $('.ec-blockTopBtn_media_pc').fadeIn();
        } else {
            $('.ec-blockTopBtn_media_pc').fadeOut();
        }

        // PC表示の時のみに適用
        if (window.innerWidth >= media_tablet_width) {

            if ($('#shopping-form').length) {

                var side = $(".ec-orderRole__summary"),
                    wrap = $("#shopping-form"),
                    min_move = wrap.offset().top,
                    max_move = wrap.height(),
                    margin_bottom = max_move - min_move;

                var scrollTop = $(window).scrollTop();
                if (scrollTop > min_move && scrollTop < max_move) {
                    var margin_top = scrollTop - min_move;
                    side.css({"margin-top": margin_top});
                } else if (scrollTop < min_move) {
                    side.css({"margin-top": 0});
                } else if (scrollTop > max_move) {
                    side.css({"margin-top": margin_bottom});
                }

            }
        }
        return false;
    });


    // Legacy SP nav toggle - unified to use is-active (hyphen) class
    $('.ec-headerNavSP').on('click', function() {
        $('.ec-layoutRole').toggleClass('is-active');
        $('.ec-drawerRole').toggleClass('is-active');
        $('.ec-drawerRoleClose').toggleClass('is-active');
        $('body').toggleClass('have-curtain');
        if ($('body').hasClass('have-curtain')) {
            $('body').css('overflow', 'hidden');
        } else {
            $('body').css('overflow', '');
        }
    });

    $('.ec-overlayRole').on('click', function() {
        $('body').removeClass('have-curtain').css('overflow', '');
        $('.ec-layoutRole').removeClass('is-active');
        $('.ec-drawerRole').removeClass('is-active');
        $('.ec-drawerRoleClose').removeClass('is-active');
    });

    $('.ec-drawerRoleClose').on('click', function() {
        $('body').removeClass('have-curtain').css('overflow', '');
        $('.ec-layoutRole').removeClass('is-active');
        $('.ec-drawerRole').removeClass('is-active');
        $('.ec-drawerRoleClose').removeClass('is-active');
    });

    // TODO: カート展開時のアイコン変更処理
    $('.ec-headerRole__cart').on('click', '.ec-cartNavi', function() {
        // $('.ec-cartNavi').toggleClass('is-active');
        $('.ec-cartNaviIsset').toggleClass('is-active');
        $('.ec-cartNaviNull').toggleClass('is-active')
    });

    $('.ec-headerRole__cart').on('click', '.ec-cartNavi--cancel', function() {
        // $('.ec-cartNavi').toggleClass('is-active');
        $('.ec-cartNaviIsset').toggleClass('is-active');
        $('.ec-cartNaviNull').toggleClass('is-active')
    });

    $('.ec-orderMail__link').on('click', function() {
        $(this).siblings('.ec-orderMail__body').slideToggle();
    });

    $('.ec-orderMail__close').on('click', function() {
        $(this).parent().slideToggle();
    });

    $('.is_inDrawer').each(function() {
        var html = $(this).html();
        $(html).appendTo('.ec-drawerRole');
    });

    $('.ec-blockTopBtn').on('click', function() {
        $('html,body').animate({'scrollTop': 0}, 500);
    });

    // スマホのドロワーメニュー内の下層カテゴリ表示
    // TODO FIXME スマホのカテゴリ表示方法
    $('.ec-itemNav ul a').click(function() {
        var child = $(this).siblings();
        if (child.length > 0) {
            if (child.is(':visible')) {
                return true;
            } else {
                child.slideToggle();
                return false;
            }
        }
    });

    // イベント実行時のオーバーレイ処理
    // classに「load-overlay」が記述されていると画面がオーバーレイされる
    $('.load-overlay').on({
        click: function() {
            loadingOverlay();
        },
        change: function() {
            loadingOverlay();
        }
    });

    // submit処理についてはオーバーレイ処理を行う
    $(document).on('click', 'input[type="submit"], button[type="submit"]', function() {

        // html5 validate対応
        var valid = true;
        var form = getAncestorOfTagType(this, 'FORM');

        if (typeof form !== 'undefined' && !form.hasAttribute('novalidate')) {
            // form validation
            if (typeof form.checkValidity === 'function') {
                valid = form.checkValidity();
            }
        }

        if (valid) {
            loadingOverlay();
        }
    });
});

$(window).on('pageshow', function() {
    loadingOverlay('hide');
});

/**
 * オーバーレイ処理を行う関数
 */
function loadingOverlay(action) {

    if (action == 'hide') {
        $('.bg-load-overlay').remove();
    } else {
        var $overlay = $('<div class="bg-load-overlay">');
        $('body').append($overlay);
    }
}

/**
 *  要素FORMチェック
 */
function getAncestorOfTagType(elem, type) {

    while (elem.parentNode && elem.tagName !== type) {
        elem = elem.parentNode;
    }

    return (type === elem.tagName) ? elem : undefined;
}

// anchorをクリックした時にformを裏で作って指定のメソッドでリクエストを飛ばす
// Twigには以下のように埋め込む
// <a href="PATH" {{ csrf_token_for_anchor() }} data-method="(put/delete/postのうちいずれか)" data-confirm="xxxx" data-message="xxxx">
//
// オプション要素
// data-confirm : falseを定義すると確認ダイアログを出さない。デフォルトはダイアログを出す
// data-message : 確認ダイアログを出す際のメッセージをデフォルトから変更する
//
$(function() {
    var createForm = function(action, data) {
        var $form = $('<form action="' + action + '" method="post"></form>');
        for (input in data) {
            if (data.hasOwnProperty(input)) {
                $form.append('<input name="' + input + '" value="' + data[input] + '">');
            }
        }
        return $form;
    };

    $('a[token-for-anchor]').click(function(e) {
        e.preventDefault();
        var $this = $(this);
        var data = $this.data();
        if (data.confirm != false) {
            if (!confirm(data.message ? data.message : eccube_lang['common.delete_confirm'] )) {
                return false;
            }
        }

        // 削除時はオーバーレイ処理を入れる
        loadingOverlay();

        var $form = createForm($this.attr('href'), {
            _token: $this.attr('token-for-anchor'),
            _method: data.method
        }).hide();

        $('body').append($form); // Firefox requires form to be on the page to allow submission
        $form.submit();
    });
});

/** theme **/

$(function() {
    // Unified drawer open/close using is-active class (hyphen convention)
    function drawerOpen() {
        $('.ec-drawerRole').addClass('is-active');
        $('.ec-headerHamburger').addClass('is-active');
        $('.ec-drawerOverlay').addClass('is-active');
        $('body').addClass('have-curtain').css('overflow', 'hidden');
    }

    function drawerClose() {
        $('.ec-drawerRole').removeClass('is-active');
        $('.ec-headerHamburger').removeClass('is-active');
        $('.ec-drawerOverlay').removeClass('is-active');
        $('body').removeClass('have-curtain').css('overflow', '');
        // Update aria-expanded on hamburger button
        $('.ec-headerHamburger').attr('aria-expanded', 'false');
    }

    function drawerToggle() {
        if ($('.ec-drawerRole').hasClass('is-active')) {
            drawerClose();
        } else {
            drawerOpen();
            $('.ec-headerHamburger').attr('aria-expanded', 'true');
        }
    }

    function cartToggle() {
        $('.ec-cartPopupRole').toggleClass('ec-cartPopupRole_is_active');
        $('.ec-headerCart .ec-cartRole').toggleClass('ec-cartRole_is_active');
    }

    // Hamburger button click
    $('.ec-headerHamburger').on('click', drawerToggle);

    // Overlay click closes drawer
    $(document).on('click', '.ec-drawerOverlay', drawerClose);

    // Drawer close button
    $(document).on('click', '.ec-drawerRole__close', drawerClose);

    // Escape key closes drawer
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('.ec-drawerRole').hasClass('is-active')) {
            drawerClose();
            $('.ec-headerHamburger').trigger('focus');
        }
    });

    $(document).on('click', '.ec-header .ec-cartRole .ec-cart', function(event) {
        event.preventDefault();
        cartToggle();
        drawerClose();
    });
    $('.ec-cartPopup__action .ec-blockBtn_cancel').on('click', cartToggle);
});
