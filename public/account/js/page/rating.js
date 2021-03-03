$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getRatingList !== "undefined") {
        ratingTable = $("#ratingTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getRatingList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "user", name: "user", width: '25%' },
                { data: "rate", name: "rate", width: '15%' },
                { data: "review", name: "review", width: '40%' },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    width: '10%'
                }
            ],
            drawCallback: function() {
                init_rating('rating_box');
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    ratingModal.on("hidden.bs.modal", function() {});
});

//edit health feed
function editReview(id) {
    if (typeof editRatingUrl !== "undefined") {
        var url = editRatingUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    ratingModal.html(response.html);
                    ratingModal.modal("toggle");
                    init_rating_form();
                    init_rating_box(response.rating.rating);
                } else {
                    //
                }
            },
            error: function() {
                //
            }
        });
    }
}

function init_rating_box(rating) {
    $(".edit-rating").starRating({
        totalStars: 5,
        initialRating: rating,
        minRating: 1,
        strokeWidth: 0,
        disableAfterRate: false,
        useFullStars: true,
        ratedColors: ['#ffa500', '#ffa500', '#ffa500', '#ffa500', '#ffa500'],
        callback: function(currentRating) {
            $('#rating').val(currentRating);
        }
    });
}
//delete drug
function deleteReview(id) {
    if (typeof deleteRatingUrl !== "undefined") {
        var url = deleteRatingUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this review ?",
                type: "warning",
                showCancelButton: true,
                customClass: "",
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    url: url,
                    type: "DELETE",
                    processData: false,
                    contentType: false,
                    dataType: "JSON",
                    data: { id: id },
                    success: function(response) {
                        if (response.status === "success") {
                            swal.close();
                            ratingTable.draw();
                        }
                    },
                    error: function() {
                        swal.close();
                    }
                });
            }
        );
    }
}

function init_rating_form() {
    ratingForm = $document.find("#ratingForm");

    //Jquery validation of form field
    ratingForm.validate({
        /*  rules: {
             rating: "required",
         },
         messages: {
             rating: "Please give rating",
         }, */
        submitHandler: function(form) {
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    ratingForm.find(".btn-submit").addClass("disabled btn-progress");
                    ratingForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        ratingModal.modal("toggle");
                        ratingForm.trigger("reset");
                        ratingTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    ratingForm.find(".btn-submit").removeClass("disabled btn-progress");
                    ratingForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}