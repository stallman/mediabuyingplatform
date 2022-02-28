$(document).ready(function (e) {
    for (let i = 1; i <= 3; i++) {
        changeFilter(i)
    }
    $('#other_settings_otherFilterParams1').on('change', function (e) {
        changeFilter(1)
    });

    $('#other_settings_otherFilterParams2').on('change', function (e) {
        changeFilter(2)
    });

    $('#other_settings_otherFilterParams3').on('change', function (e) {
        changeFilter(3)
    });

    $('#other_settings').submit(function(e) {
        $(this).append($("#other_settings_otherFilterValues1"));
        $(this).append($("#other_settings_otherFilterValues2"));
        $(this).append($("#other_settings_otherFilterValues3"));
    });

    function changeFilter(elemIndex) {
        let filterParams = $("#other_settings_otherFilterParams" + elemIndex + "")
        let filterValues = $("#other_settings_otherFilterValues" + elemIndex + "")
        let form = $(this).closest('form');
        let data = {};
        data[filterParams.attr('name')] = filterParams.val();
        ajaxGetFormField(form, data, filterValues, elemIndex)
    }

    function ajaxGetFormField(form, data, filterValues, elemIndex) {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: data,
            success: function (html) {
                $(".wrapper-settings-form-value-" + elemIndex + "").find(".select2").remove()
                $(filterValues).replaceWith(
                    $(html).find("#other_settings_otherFilterValues" + elemIndex + "")
                );
                $(".multiple-selector").select2()
                $(filterValues).prop("disabled", false)
            }
        });
    }

    if (localStorage.getItem('otherSettingsFormIsHide') === 'hidden') {
        $(".slide").hide();
    } else {
        $(".slide").show();
    }

    $(".toggle-other-settings-form").click(function () {

        $('.slide').slideToggle(function () {
            localStorage.setItem('otherSettingsFormIsHide', $(this).is(':hidden') ? 'hidden' : 'visible');
            if($(this).is(':hidden') && $('.other-settings-form').hasClass('fa-angle-down')) {
                $('.other-settings-form').removeClass('fa-angle-down');
                $('.other-settings-form').addClass('fa-angle-up');
            } else {
                $('.other-settings-form').removeClass('fa-angle-up');
                $('.other-settings-form').addClass('fa-angle-down');
            }
        });
    });

    if (localStorage.getItem('essentialSettingsFormIsHide') === 'hidden') {
        $(".slide-essential-fields-of-settings").hide();
    } else if (localStorage.getItem('essentialSettingsFormIsHide') === 'visible') {
        $(".slide-essential-fields-of-settings").css({'display': 'block'})
    }

    $(".settings-of-fields-title-block").click(function () {

        $('.slide-essential-fields-of-settings').slideToggle(function () {
            localStorage.setItem('essentialSettingsFormIsHide', $(this).is(':hidden') ? 'hidden' : 'visible');
        });

        $(this).find('i').toggleClass('fa-angle-down');
        $(this).find('i').toggleClass('fa-angle-up');

    });

    set_angles();

    function set_angles() {
        if (localStorage.getItem('essentialSettingsFormIsHide') === 'hidden' &&
            $(".settings-of-fields-title-block").find('i').hasClass('fa-angle-down')) {

            $(".settings-of-fields-title-block").find('i').removeClass('fa-angle-down');
            $(".settings-of-fields-title-block").find('i').addClass('fa-angle-up');

        } else if (localStorage.getItem('essentialSettingsFormIsHide') === 'visible' &&
            $(".settings-of-fields-title-block").find('i').hasClass('fa-angle-up')) {

            $(".settings-of-fields-title-block").find('i').removeClass('fa-angle-up');
            $(".settings-of-fields-title-block").find('i').addClass('fa-angle-down');

        }
    }

    checkDropTrafficInput();
    $('#other_settings_blackListParams').on('change', function (e) {
        checkDropTrafficInput();
    });

    function checkDropTrafficInput() {
        if ($("#other_settings_blackListParams option:selected").text() != '') {
            $('.drop-traffic').show()
        } else {
            $('.drop-traffic').hide()
        }
    }
})
