if (typeof jQuery == 'undefined') {
    console.log('ERR: jQuery is not loaded yet!');
} else {
    var $ = jQuery;
    jQuery(document).ready(function() {
        if ($('.cms2cms-connector-container').length > 0) {

            /**
             * Init function
             */
            var init = function () {
                checkDoneTab();
                if ($('.active').hasClass('step-sign')) {
                    toggleSignIN();
                    singInSubmit();
                } else {
                    $(document).on('click', '#verifySource_connector', function (event) {
                        event.preventDefault();
                        validateSource();
                    });
                    $('#sourceUrl').keypress(function(event) {
                        if(event.which == 13) {
                            event.preventDefault();
                            validateSource();
                        }
                    });
                }

                authTimeOutCheck();

                $('#logout').on('click', 'button', function () {
                    $('#logout').submit();
                });
            };

            /**
             * Toggle sing in tabs
             */
            var singInSubmit = function () {
                $('#auth_submit').on('click', function () {
                    var form = $('.active').next().find('form');

                    logOut();

                    $('#cms_overlay').fadeIn();
                    $.ajax({
                        url: form.attr('action'),
                        type: 'post',
                        data: form.serialize(),
                        dataType: 'jsonp',
                        success: function (data) {
                            if (data.key) {
                                saveUser({
                                    action: 'cms2cms_connector_save_options',
                                    accessKey: data.key,
                                    login: $(form).find('[name=email]').val()
                                });
                            } else {
                                showErrors(data.errors);
                                $('#cms_overlay').fadeOut();
                            }
                        }
                    });
                });
            };

            /**
             * Log out function
             */
            var logOut = function () {
                var url = ($('#logout').length > 0) ? $('#logout').attr('data-logout') : config.logout;
                $.ajax({
                    url: config.auth_check,
                    type: 'get',
                    dataType: 'jsonp',
                    timeout: 20000,
                    success: function (data) {
                        if (data.length > 0) {
                            $.ajax({
                                url: url,
                                type: 'post',
                                dataType: 'jsonp',
                                timeout: 20000,
                                success: function () {
                                    if ($('#logout').length > 0)
                                        $('#logout').submit();
                                },
                                complete: function (xhr, textStatus) {
                                    if (textStatus == 'parseeror' && $('#logout').length > 0) {
                                        $('#logout').submit();
                                    }
                                },
                                error: function(jqXHR) {
                                    $('#logout').submit();
                                }
                            });
                        }
                    }
                });

            };

            /**
             * Save user
             * @param data
             */
            var saveUser = function (data) {
                $.post(ajaxurl, data, function (data) {
                    if (!data.errors) {
                        window.location.href = window.location.pathname + window.location.search;
                    } else {
                        showErrors(data.errors);
                    }
                });
            };

            /**
             * Show errors
             * @param errors
             */
            var showErrors = function (errors) {
                $('.cms2cms-connector-error').hide();
                for (var error in errors) {
                    var message = (Object.values(errors[error])[0]) ? Object.values(errors[error])[0] : 'Invalid data';
                    $('.cms2cms-connector-error.' + error + ' span').html(message).parent().fadeIn();
                }
            };

            /**
             * Toggle login and register tab
             */
            var toggleSignIN = function () {
                var cms2cmsBlock = $('#cms2cms_connector_accordeon');
                var signInTabs = cms2cmsBlock.find('a.nav-tab');

                signInTabs.on('click', function (e) {
                    if (!$(this).hasClass('cms2cms-connector-real-link')) {
                        e.preventDefault();
                        var activeClass = 'nav-tab-active';
                        if (!$(this).hasClass(activeClass)) {
                            signInTabs.removeClass(activeClass);
                            $(this).addClass(activeClass);
                            $(this).closest('.cms2cms_connector_accordeon_item').find('form').attr('action', $(this).attr('href'));
                        }
                    }
                });

                var formContent = $('.cms2cms_connector_accordeon_item_register').find('.center-content'),
                    button = $('.login-reg');

                button.on('click', function () {
                    formContent.toggleClass('login-cms');
                    formContent.parent('form').attr('action', ($('.login-cms').length) ? $('#registerUrl').val() : $('#loginUrl').val());
                    $('.user-name-block').fadeToggle();
                    $('.cms2cms-connector-real-link').fadeToggle();
                    button.parent('p.account-register').toggle();
                    button.parent('p.account-login').toggle();
                    $('.cms2cms-connector-error span').parent().fadeOut();

                    $('#signIn').toggle();
                    $('#signUp').toggle();
                });
            };

            /**
             * Check step for done and mark it
             */
            var checkDoneTab = function () {
                $('#cms_overlay').prependTo('#wpwrap');
                if ($('.cms2cms-connector-container').hasClass('cms2cms_connector_is_activated')) {
                    $('body').find('.step-sign').removeClass('active').addClass('done');
                    $('body').find('.step-connect').addClass('active');
                }

                jQuery.fn.center = function () {
                    this.css("position", "fixed");
                    this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
                        $(window).scrollTop()) + "px");
                    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $('#adminmenu').width() / 2 +
                        $(window).scrollLeft()) + "px");
                    return this;
                };
                $(window).resize(function () {
                    $('.circle-an').center();
                });

                $('.circle-an').center();
            };

            /**
             * Auth check function
             */
            var authCheck = function () {
                $.ajax({
                    url: config.auth_check,
                    type: 'get',
                    dataType: 'jsonp',
                    timeout: 20000,
                    success: function (data) {
                        if (data.length == 0 && $('#logout').length > 0 || data.key && $('#logout').length == 0 || data.key != $('#key').val()) {
                            $('#logout').submit();
                        }
                    }
                });
            };
            /**
             * Auth timeout function check
             */
            var authTimeOutCheck = function () {
                authCheck();
                if ($('#logout').length > 0) {
                    setInterval(authCheck, 60000);
                }
            };

            init();
        };
    });
}
