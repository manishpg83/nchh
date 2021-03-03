/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

function readURL(input, id) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $('#' + id).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]); // convert to base64 string
    }
}

function init_doctor_dropdown(identity) {
    var paginate_count = 10;
    identity.select2({
        ajax: {
            url: getDoctorsUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term || '', // search term
                    page: params.page || 1,
                    selected_value: existing_doctors || {}, // search term
                    paginate_count: paginate_count || 10
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * paginate_count) < data.total_count
                    }
                };
            },
            cache: true
        },
        placeholder: 'Search Doctors',
        minimumInputLength: 1,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection

    });
}

function formatRepo(repo) {
    if (repo.loading) {
        return repo.text;
    }

    var $container = $(
        "<div class='select2-result-repository clearfix'>" +
        "<div class='select2-result-repository__avatar'><img src='" + repo.profile_picture + "' width='20' /></div>" +
        "<div class='select2-result-repository__meta'>" +
        "<div class='select2-result-repository__title'></div>" +
        "<div class='select2-result-repository__degree'></div>" +
        "<div class='select2-result-repository__address'></div>" +
        "<div class='select2-result-repository__description'></div>" +
        "<div class='select2-result-repository__statistics'>" +
        "</div>" +
        "</div>" +
        "</div>"
    );

    $container.find(".select2-result-repository__title").html(repo.name + '<span class="bullet"></span>' + repo.phone);
    if (repo.detail.degree != null) {
        $container.find(".select2-result-repository__degree").text(repo.detail.degree);
    }
    if (repo.city && repo.state) {
        $container.find(".select2-result-repository__address").text(repo.city + ', ' + repo.state);
    }
    return $container;
}

function formatRepoSelection(repo) {
    return repo.name || repo.text;
}

$.validator.addMethod('valid_phone', function(value, element) {
    return this.optional(element) || value.length >= 10;
    // if (value.trim()) {
    //     return this.optional(element) || iti.isValidNumber();
    // }
}, 'Please enter valid mobile number');