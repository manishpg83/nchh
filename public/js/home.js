$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });

});

$(window).scroll(function () {
    let $this = $(this);

    if (typeof enable_header == "undefined") {
        if ($this.scrollTop() > $(".hero").outerHeight() - 150) {
            $(".main-navbar").addClass("bg-dark");
        } else {
            $(".main-navbar").removeClass("bg-dark");
        }
    }

    $("section").each(function () {
        if ($this.scrollTop() >= ($(this).offset().top - $(".main-navbar").outerHeight())) {
            $(".smooth-link").parent().removeClass("active");
            $(".smooth-link[href='#" + $(this).attr("id") + "']").parent().addClass('active');
        }
    });
});

