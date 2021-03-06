<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <meta content="width=device-width" name="viewport" />
    <meta content="IE=edge" http-equiv="X-UA-Compatible" />
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css" />
</head>
<style>
    @page {
        margin: 0px;
    }

    body {
        margin: 0px;
    }

    /*Global Class*/
    .m-t-5 {
        margin-top: 20px;
    }

    .m-t-10 {
        margin-top: 10px;
    }

    .m-t-15 {
        margin-top: 15px;
    }

    .m-t-20 {
        margin-top: 20px;
    }

    .m-t-25 {
        margin-top: 25px;
    }

    .m-t-35 {
        margin-top: 35px;
    }

    .m-t-45 {
        margin-top: 45px;
    }

    .p-t-10 {
        padding-top: 10px;
    }

    div#container {
        font: normal 16px 'Lato', Tahoma, Verdana, Segoe, Sans-serif;
        background: white;
        color: #052d3d;
    }

    header {
        width: 100%;
        background: #000;
        height: 380px;
        clear: both;
        color: #fff;
    }

    header .top_div {
        width: 100%;
        height: 125px;
    }

    header .info {
        padding: 0 25px;
    }

    header .info .left {
        float: left;
        width: 33%;
    }

    header .info .center {
        float: left;
        width: 33%;
        text-align: center;
    }

    header .info .center #prefix {
        text-transform: uppercase;
        font-size: 25px;
    }

    header .info .right {
        float: right;
        width: 33%;
        text-align: right;
    }

    header img.company-logo {
        margin: 30px 0 0 30px;
    }

    header .company-address {
        float: right;
        margin: 30px;
        font-size: 20px;
        text-align: right;
        width: 40%;
    }

    header .company-address .subtitle {
        font-size: 12px;
    }



    main {
        margin: 30px 25px;
    }

    main table thead {
        background-color: #eeeeee;
        border: none;
    }

    main table thead th {
        height: 35px;
        text-align: center
    }

    main table thead th:first-child {
        text-align: left;
        padding-left: 10px
    }

    main table thead th {
        text-align: center;
    }

    main table tbody tr td {
        text-align: right;
        padding: 6px 6px 10px 0;
    }

    main table tbody tr td:first-child {
        text-align: left;
        padding-left: 10px;
    }

    main table tbody tr.subtotal_div td {
        border-bottom: none;
    }

    main span.rupee_sign img {
        width: 14px;
    }

    td {
        border-bottom: 1px solid #ddd;
        /*margin: 5px;*/
    }

    .blank_row td {
        line-height: 30px;
        background-color: #FFFFFF;
        border-bottom: none;
    }

    table.table_subtotal tr td {
        border-bottom: 1px solid #ddd !important;
    }

    table.table_subtotal tr td.row_paid {
        background-color: #eeeeee;
        border: none !important;
    }

    .seprator {
        width: 100%;
        border: dotted 1px #052d3d;
        height: 2px;
        position: absolute;
    }

    .service_agreement_content {
        width: 100%;
    }

    .service_agreement_content .title {
        width: 100%;
        text-align: center;
        position: relative;
        margin: 25px 0 25px 0;
        font-style: italic;
        text-decoration: underline;
    }

    .service_agreement_content .content {
        width: 100%;
        padding: 0 25px;
        font-size: 13px;
        text-align: justify;
        text-justify: inter-word;
    }

    .service_agreement_content .content .sub_title {
        margin-top: 15px;
        padding-bottom: 10px;
        font-weight: bolder;
        font-size: 14px;
    }

    .service_agreement_content .content .paragraph {
        margin-bottom: 5px;
    }

    .service_agreement_content .content ul {
        padding-left: 25px;
        margin-top: 0;
    }

    .service_agreement_content ol.lower-alpha-list {
        list-style-type: lower-alpha;
        margin-top: 0;
        padding-left: 30px;
    }

    .service_agreement_content ol.numberic-list {
        margin-top: 0;
        padding-left: 30px;
    }

    .page_break {
        page-break-before: always;
    }

    footer {
        width: 100%;
        text-align: center;
        /*margin: 15px;*/
        position: absolute;
        bottom: 20px;
    }
</style>

<body>
    <div id="container">
        <header>
            <div class="top_div">
                <img class="company-logo" src="http://74.207.248.179/neucrad/public/images/logo/nc_one.png">
                <div class="company-address">
                    <span class="title">NC Health Hub</span><br>
                    <span class="subtitle">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</span>
                </div>
            </div>
            <div class="info">
                <div class="left">
                    <span style="font-size: 14px;font-weight:bold;">Patient's Detail,</span> <br>
                    {{$appointment->patient->name}},<br>
                    {{$appointment->patient->phone}},<br>
                    {!!$appointment->patient->full_address!!}
                </div>
                <div class="center">
                    <span id="prefix">Invoice </span>
                    <br>Invoice Id : #{{$appointment->payment_id}}
                    <br>Date : {{date('d/m/Y', strtotime($appointment->payment->created_at))}}
                </div>
                @if(isset($appointment->practice_id))
                <div class="right">
                    <span style="font-size: 14px;font-weight:bold;">Practice's Detail,</span> <br>
                    {{$appointment->practice->name}},<br>
                    {{$appointment->practice->email}},<br>
                    {{$appointment->practice->phone}},
                    <br>
                    {!!$appointment->practice->full_address!!},
                </div>
                @endif
                @if(isset($appointment->diagnostics_id))
                <div class="right">
                    <span style="font-size: 14px;font-weight:bold;">diagnostics's Detail,</span> <br>
                    {{$appointment->diagnostics->name}},<br>
                    {{$appointment->diagnostics->detail->degree}},<br>
                    {{$appointment->diagnostics->detail->experience}} Years of experience overall
                    <br>
                    {!!$appointment->practice->full_address!!},
                </div>
                @endif
            </div>
    </div>
    </div>
    </header>
    <main>
        <table cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Desciption</th>
                    <th style="text-align: right;padding-right: 10px;">Date & Time</th>
                    <th style="text-align: right;padding-right: 10px;">Quantity</th>
                    <th style="text-align: right;padding-right: 10px;">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{$appointment->type($appointment->id)}}</td>
                    <td>{{date('l jS F, \a\t h:i a', strtotime($appointment->start_time))}}</td>
                    <td>1</td>
                    <td>{{$price}}</td>
                </tr>
                <tr class="blank_row">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom: none"></td>
                    <td style="text-align: left;">Total</td>
                    <td>{{$price}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom: none"></td>
                    <td style="text-align: left;">Tax <small>(18%)</small></td>
                    <td>{{$gst}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom: none"></td>
                    <td style="text-align: left;">Discount</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-bottom: none"></td>
                    <td  class="row_paid" style="text-align: left;"><b>Total Amount</b></td>
                    <td  class="row_paid" style="vertical-align:middle;">
                        <b>{{$appointment->payment->amount}}</b>
                    </td>
                </tr>

                
            </tbody>
        </table>
    </main>
    <div style="width: 100%;text-align: center;position: relative;padding: 15px 0 15px 0;font-style: italic;">
        * Tax will be levied as and when applicable.
    </div>

    <footer>
        <span class="title">NC Health Hub</span><br>
        <span>{{$appointment->practice->phone}} | {{$appointment->practice->email}}</span>

    </footer>

    <div class="page_break"></div>

    <div class="service_agreement_content">
        <div class="title p-t-10">Service Agreement between NC Health Hub's Registered Doctor and the Registered Patient</div>

        <div class="content">

            <div class="paragraph">
                <div class="sub_title">1. <u>Introduction:</u></div>
                Lorem ipsum, or lipsum as it is sometimes known, is dummy text used in laying out print, graphic or web designs. The passage is attributed to an unknown typesetter in the 15th century who is thought to have scrambled parts of Cicero's De Finibus Bonorum et Malorum for use in a type specimen book.Lorem ipsum, or lipsum as it is sometimes known, is dummy text used in laying out print, graphic or web designs. The passage is attributed to an unknown typesetter in the 15th century who is thought to have scrambled parts of Cicero's De Finibus Bonorum et Malorum for use in a type specimen book.
            </div>
        </div>
    </div>
    </div>
</body>

</html>