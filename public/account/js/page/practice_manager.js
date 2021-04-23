$document = $(document);
$document.ready(function() {
    /*Pass csrf token for every ajax call*/
    $.ajaxSetup({ headers: header });

    if (typeof practiceList !== "undefined" && $('#practiceTable').length > 0) {
        practiceTable = $('#practiceTable').DataTable({
            // dom: "<'row'<'col-xs-12 col-lg-12't>><'row'<'col-lg-6'i><'col-lg-6'p>>",
            responsive: true,
            processing: true,
            serverSide: true,
            /*ajax: courseList,*/
            ajax: {
                url: practiceList,
                data: function(d) {
                    // d.search = $('input[type=search]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'name', width: '15%' },
                { data: 'email', width: '10%', sortable: false, searchable: false },
                { data: 'phone', width: '10%', sortable: false, searchable: false },
                { data: 'locality' },
                { data: 'address' },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function() {
                init_tooltip();
            },
            drawCallback: function() {
                init_tooltip();
            }
        });
    }

    /* coursefilter.find('input[type=search]').on('keyup', function (e) {
        courseTable.search(this.value).draw();
    }); */

    // $('select[name="skill[]"],select[name=interest]').change(function (e) {
    //     courseTable.draw();
    // });

    // $(document).find("select.js-multiple").select2();


    /*Start:validation error placement*/
    $.validator.setDefaults({
        /*errorClass: 'errorField', errorElement: 'div',*/
        errorPlacement: function(error, element) {
            if (element.attr("name") === "skills[]") {
                element.parent().find("span.select2-container").after(error);
            } else {
                error.insertAfter(element);
            }
        }
    });
    /*End:validation error placement*/

    if ($('.browse_file').length > 0) {
        $('.browse_file').change(function(e) {
            // var fileName = e.target.files[0].name;
            // alert('The file "' + fileName + '" has been selected.');
            readURL(this, 'imagePreview');
        });
    }

    if ($('#PracticeForm').length > 0) {
        init_practice_form();
    }

    $(".select2_field").select2();

    initMap()
});
let time = 0;
$('input[name="address"]').on('keydown', function() {
    clearTimeout(time);

    var val = $(this).val();
    time = setTimeout(function() {
        changeMapMarker(val);
    }, 500);
});

function init_practice_form() {
    PracticeForm.validate({
        rules: {
            name: {
                required: true,
                normalizer: function(value) {
                    return $.trim(value);
                }
            },
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                number: true,
            },
            address: {
                required: true
            },
            locality: {
                required: true
            },
            logo: {
                extension: "jpg|jpeg|png|JPG|JPEG|PNG|gif"
            },
            description: {
                required: true
            },
            city: {
                required: true
            },
            country: {
                required: true
            },
            pincode: {
                required: true
            },
            fees: {
                required: true,
                number: true,
                min: 1
            },
        },
        messages: {
            email: { email: 'Enter a valid email address.' },
        },
        submitHandler: function(form) {
            var lati = $(form).find('#latitude').val();
            var get_timing = $('#timing_chart').jqs('export');
            if (lati == 0.0) {
                toastrAlert("error", 'Practice', 'Please choose location on google map.');
                return false;
            }
            var count = 0;
            JSON.parse(get_timing).forEach(element => {
                if (element.periods.length !== 0) {
                    count++;
                }
            });

            if (count > 0) {
                $('#field_timing').val(get_timing);
                $('#submit').attr('disabled', 'disabled');
                form.submit();
            } else {
                toastrAlert('error', 'Timing', 'Please select practice timing')
            }
        }
    });

    $('#timing_chart').jqs({
        mode: 'edit',
        hour: 24,
        days: 7,
        periodDuration: 60,
        data: parseValue,
        periodOptions: false,
        periodColors: [],
        periodTitle: '',
        periodBackgroundColor: 'rgba(82, 155, 255, 0.5)',
        periodBorderColor: '#2a3cff',
        periodTextColor: '#000',
        periodRemoveButton: 'Remove',
        // periodDuplicateButton: 'Duplicate',
        periodTitlePlaceholder: 'Title',
        daysList: [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ],
        onInit: function() {},
        onAddPeriod: function(period, jqs) {},
        onRemovePeriod: function() {
            var get_timing = $('#timing_chart').jqs('export');
        },
        onDuplicatePeriod: function() {},
        onClickPeriod: function() {}
    });

    // $('#timing_chart').slimscroll({
    //     // alwaysVisible: true,
    //     height: 500
    // });
}


/* Start: Map Pin Location */
var infoMap;
function initMap() {
    var myLatlng = { lat: lati, lng: long };
    var map = new google.maps.Map(document.getElementById('map_canvas'), { zoom: 12, center: myLatlng });

    addMarker(myLatlng, 'Default Marker', map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            map.setCenter(pos);
            if (lati == 0 && lati == 0) {
                addMarker(pos, 'Current Location', map);
            }
        }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
        });
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, infoWindow, map.getCenter());
    }
}

function handleEvent(event) {
    document.getElementById('latitude').value = event.latLng.lat();
    document.getElementById('longitude').value = event.latLng.lng();

    $latitude = event.latLng.lat();
    $longitude = event.latLng.lng();
    
    getAddress($latitude,$longitude);
}

function addMarker(latlng, title, map) {
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: title,
        draggable: true
    });

    marker.addListener('drag', handleEvent);
    marker.addListener('dragend', handleEvent);
}

function getAddress(latitude,longitude) {

    LocationUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng="+ latitude +","+ longitude +"&key="+ googleMapApi +"";
               
    $.ajax({
        url: LocationUrl, 
        type: "GET",   
        beforeSend: function(jqXHR, settings) {
            delete $.ajaxSettings.headers["X-CSRF-TOKEN"];
        },
        cache: false,
        success: function(response){                          
            let data = response.results[0].address_components;
            let userAddress = data[0].long_name +  ', ' + data[1].long_name + ', ' + data[2].long_name;
            $('input[name="address"]').val(userAddress) 
        }           
    });
}

function changeMapMarker(val) {

    var myLatlng = { lat: lati, lng: long };
    var map = new google.maps.Map(document.getElementById('map_canvas'), { zoom: 12, center: myLatlng });
    const geocoder = new google.maps.Geocoder();
    if(val != '') {
        geocoder.geocode({ address: val }, (results, status) => {
            if (status === "OK") {
                map.setCenter(results[0].geometry.location);
                addMarker(results[0].geometry.location, val, map);
            } else {
                alert('Address not found');
            }
          });
        
    }
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
      browserHasGeolocation
        ? "Error: The Geolocation service failed."
        : "Error: Your browser doesn't support geolocation."
    );
    infoWindow.open(map);
  }
  

/* End: Map Pin Location */