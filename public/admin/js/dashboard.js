$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof doctorChartData !== "undefined") {
        $.ajax({
            headers: header,
            url: doctorChartData,
            type: 'post',
            success: function(res) {
                if (res.doctor_chart.length !== 0) {
                    $("#year").html(res.data.total_doctor_year);
                    $("#month").html(res.data.total_doctor_month);
                    $("#week").html(res.data.total_doctor_week);
                    $("#today").html(res.data.total_doctor_today);
                    init_doctor_chart(res.doctor_chart);
                } else {
                    $("#doctorChart").hide(500);
                }
            },
            error: function() {
                //
            },
            complete: function() {
                //
            }
        });
    }
});
$("#datepicker").datepicker({
    format: " yyyy",
    viewMode: "years",
    minViewMode: "years"
});

$(function() {
    $document.find(".doctor-list").niceScroll({
        scrollspeed: 400,
    });
});

function init_doctor_chart($data) {
    var statistics_chart = document.getElementById("doctorChart").getContext('2d');
    var myChart = new Chart(statistics_chart, {
        type: 'line',
        data: {
            labels: $data.months,
            datasets: [{
                label: 'Registration ',
                data: $data.value,
                borderWidth: 5,
                borderColor: '#6777ef',
                backgroundColor: 'transparent',
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6777ef',
                pointRadius: 4
            }]
        },
        options: {
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        stepSize: 150
                    }
                }],
                xAxes: [{
                    gridLines: {
                        color: '#fbfbfb',
                        lineWidth: 2
                    }
                }]
            },
        }
    });
}

$(function() {
    function count($this) {
        if ($this.data('count') != 0) {
            var current = parseInt($this.html(), 10);
            $this.html(++current);
            if (current !== $this.data('count')) {
                setTimeout(function() { count($this) }, 100);
            }
        }
    }
    $("#count").each(function() {
        $(this).data('count', parseInt($(this).html(), 10));
        $(this).html('0');
        count($(this));
    });
});