@extends('backpack::layout')

@section('header')
<section class="content-header">
    <h1>
        Product chart<small>Chart of prices and rank changes</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'panel')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">Product chart</li>
    </ol>
</section>
@endsection


@section('content')
<div class="row">
    <div class="col-md-12">

    <!-- Styles -->
    <style>
        #chartdiv {
            width	: 100%;
            height	: 500px;
        }
    </style>

    <!-- Resources -->
    <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
    <script src="https://www.amcharts.com/lib/3/serial.js"></script>
    <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

    <!-- Chart code -->
    <script>
    var chart = AmCharts.makeChart("chartdiv", {
        "type": "serial",
        "theme": "light",
        "marginTop":80,
        "marginLeft":80,
        "marginRight": 40,
        "autoMarginOffset": 20,
        "mouseWheelZoomEnabled":true,
        "dataDateFormat": "YYYY-MM-DD",
        "dataProvider": [
        @foreach ($data as $d => $v)
        @if ($loop->first)
        {
        @else
        ,{
        @endif

            "date": "{{ $d }}",
            "rank": {{ $v['rank'] }},
            "regular_price": {{ $v['regular_price'] }},
            "buying_price": {{ $v['buying_price'] }}
        }
        @endforeach
        ],
        "valueAxes": [{
            "id": "v1",
            "reversed": true,
            "axisAlpha": 0,
            "position": "left",
            "ignoreAxisWidth": true
        },{
            "id": "v2",
            "axisAlpha": 0,
            "position": "right",
            "ignoreAxisWidth": true
        }],
        "graphs": [{
            "id":"g1",
            "valueAxis": "v1",
            "balloon":{
                "drop":true,
                "adjustBorderColor":false,
                "color":"#ffffff"
            },
            "balloonText": "[[date]]<br><b><span style='font-size:14px;'>[[rank]]</span></b>",
            "bullet": "round",
            "bulletSize": 8,
            "bulletBorderAlpha": 1,
            "bulletColor": "#FFFFFF",
            "lineColor": "#00ff00",
            "hideBulletsCount": 50,
            "title": "Rank",
            "lineThickness": 2,
            "useLineColorForBulletBorder": true,
            "negativeLineColor": "#637bb6",
            "valueField": "rank"
        },{
            "id":"g2",
            "valueAxis": "v2",
            "balloon":{
                "drop":true,
                "adjustBorderColor":false,
                "color":"#ffffff"
            },
            "balloonText": "[[date]]<br><b><span style='font-size:14px;'>[[buying_price]]</span></b>",
            "bullet": "round",
            "bulletSize": 8,
            "bulletBorderAlpha": 1,
            "bulletColor": "#FFFFFF",
            "lineColor": "#d1655d",
            "hideBulletsCount": 50,
            "title": "Price",
            "lineThickness": 2,
            "useLineColorForBulletBorder": true,
            "negativeLineColor": "#637bb6",
            "valueField": "buying_price"
        }],
        "chartScrollbar": {
            "graph": "g1",
            "oppositeAxis":false,
            "offset":30,
            "scrollbarHeight": 80,
            "backgroundAlpha": 0,
            "selectedBackgroundAlpha": 0.1,
            "selectedBackgroundColor": "#888888",
            "graphFillAlpha": 0,
            "graphLineAlpha": 0.5,
            "selectedGraphFillAlpha": 0,
            "selectedGraphLineAlpha": 1,
            "autoGridCount":true,
            "color":"#AAAAAA"

        },
        "chartCursor": {
            "pan": true,
            "valueLineEnabled": true,
            "valueLineBalloonEnabled": true,
            "cursorAlpha":1,
            "cursorColor":"#258cbb",
            "limitToGraph":"g1",
            "valueLineAlpha":0.2,
            "valueZoomable":true
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "dashLength": 1,
            "minorGridEnabled": true
        },
        "export": {
            "enabled": false
        }
    });

    </script>

    <!-- HTML -->
    <div id="chartdiv"></div>

    </div>
</div>
@endsection
