$document = $(document);
var consultant_fees_slider, fees_between;
$document.ready(function() {

    /*Pass csrf token for every ajax call*/
    $.ajaxSetup({ headers: header });


    $(window).scroll(function() {

        console.log($(window).scrollTop() + ' >== ' + (outerdiv.outerHeight(true) + 110));
        console.log('max_page: ' + max_page + '  > ' + page);
        console.log('Flag: ' + flag);
        /* $(window).scrollTop() >= outerdiv.outerHeight(true) + 150 */
        if (($(window).scrollTop() >= outerdiv.outerHeight(true) + 110) && (max_page > page) && flag) {
            console.log($(window).scrollTop() + ' >== ' + outerdiv.outerHeight(true));
            console.log('page = ' + page);
            page++;
            flag = 0;
            loadMoreData(page);
        }
    });

    $('.common_selector').click(function() {
        /*reset pagination*/
        page = 1;
        fetch_data(page);
    });

    $("#txt_search").keyup(function() {
        var search = $(this).val();
        page = 1;
        fetch_data(page);
        if (search != "") {}
    });

    consultant_fees_slider = new Slider("input.consultant_fees_slider", {
        // tooltip: 'always'
        formatter: function(value) {
            if (value < 0) {
                return '₹' + value + ' - ' + ('₹' + (value + 200));
            } else if (value >= 200) {
                return '₹' + value + ' - ' + ('₹' + (value + 300));
            } else {
                return '₹' + value + ' - ' + ('₹' + (value + 100));
            }
        }
    }).on('change', function(event) {
        console.log(event.oldValue + ' - ' + event.newValue);
        // console.log(event.newValue)
        if (event.newValue < 0) {
            fees_between = event.newValue + '#' + (event.newValue + 200);
        } else if (event.newValue >= 200) {
            fees_between = event.newValue + '#' + (event.newValue + 300);
        } else {
            fees_between = event.newValue + '#' + (event.newValue + 100);
        }
        if (event.newValue > 0) {}
        fetch_data(page);
    });

    init_rating('rating_box');
});


function get_filter(class_name) {
    var filter = [];
    $('.' + class_name + ':checked').each(function() {
        filter.push($(this).val());
    });
    return filter;
}

function loadMoreData(page) {
    console.log('Load more');

    var gender = get_filter('gender');
    var consult_as = get_filter('consult_as');
    var txt_keyword = $("#txt_search").val() || keyword;

    var pageURL = $(location).attr("href");
    var url = '?page=' + page;
    if (typeof pageURL !== "undefined") {
        if (pageURL.indexOf('?') !== -1) {
            url = $(location).attr("href") + '&page=' + page;
        } else {
            url = $(location).attr("href") + '?page=' + page;
        }
    }

    $.ajax({
        url: url,
        // method: 'get',
        data: { 'gender': gender, 'consult_as': consult_as, 'keyword': txt_keyword, 'search_by': 'paging' },
        beforeSend: function() {
            $('.ajax-load').show();
        }
    }).done(function(response) {
        if (response.html === " ") {
            $('.ajax-load').html("No more records found");
            return;
        }
        renderview.find('#child_container').append(response.html);
        $('.ajax-load').hide();
        init_rating('rating_box');
        flag = 1;
    }).fail(function(jqXHR, ajaxOptions, thrownError) {
        /* alert('server not responding...'); */
        toastrAlert('error', 'Search...', 'server not responding...');
    });
}

function fetch_data(page) {
    if (typeof renderview !== 'undefined') {

        var gender = get_filter('gender');
        var consult_as = get_filter('consult_as');
        var consult_fee = fees_between || '';
        var txt_keyword = $("#txt_search").val() || keyword;

        /* var by_location = $('#by_location').val();
        var plan = get_filter('plan');
        var with_rating = get_filter('rating'); */

        var pageURL = $(location).attr("href");
        var url = '?page=' + page;
        if (typeof pageURL !== "undefined") {
            if (pageURL.indexOf('?') !== -1) {
                url = $(location).attr("href") + '&page=' + page;
            } else {
                url = $(location).attr("href") + '?page=' + page;
            }
        }

        $.ajax({
            url: url,
            // url: '?keyword=' + keyword,
            data: { 'gender': gender, 'consult_as': consult_as, 'consult_fee': consult_fee, 'keyword': txt_keyword },
            beforeSend: function() {},
            success: function(response) {
                $('.search-string-div').html(response.search_string);
                renderview.html('');
                renderview.html(response.html);
                init_rating('rating_box');
                max_page = response.last_page;
            }
        });
    }

}