$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(success, error, options);
    }
});

var options = {
    enableHighAccuracy: true,
    timeout: 50000,
    maximumAge: 0
  };
  
function success(pos) {
    var crd = pos.coords;

    LocationUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng="+ crd.latitude +","+ crd.longitude +"&key="+ googleMapApi +"";

	delete $.ajaxSettings.headers["X-CSRF-TOKEN"]; // Remove header before call        
    $.ajax({
        url: LocationUrl, 
        type: "GET",
        cache: false,
        success: function(response){                          
            console.log(response);
            let data = response.results[0].address_components[3].long_name;
            console.log(data);
            $('input[name="location"]').val(data);
        }           
    });
	$.ajaxSettings.headers["X-CSRF-TOKEN"] = $('meta[name="csrf-token"]')[0].content;
}

function error(err) {
    // console.warn(`ERROR(${err.code}): ${err.message}`);
}
  
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

