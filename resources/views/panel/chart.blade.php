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
        "marginTop":0,
        "marginRight": 80,
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
            "position": "left"
        },{
            "id": "v2",
            "axisAlpha": 0,
            "position": "right"
        }],
        "graphs": [{
            "id":"g1",
            "valueAxis": "v1",
            "balloonText": "[[date]]<br><b><span style='font-size:14px;'>[[rank]]</span></b>",
            "bullet": "round",
            "bulletSize": 8,
            "lineColor": "#d1655d",
            "lineThickness": 2,
            "negativeLineColor": "#637bb6",
            "type": "smoothedLine",
            "valueField": "rank"
        },{
            "id":"g2",
            "valueAxis": "v2",
            "balloonText": "[[date]]<br><b><span style='font-size:14px;'>[[buying_price]]</span></b>",
            "bullet": "round",
            "bulletSize": 8,
            "lineColor": "#d1655d",
            "lineThickness": 2,
            "negativeLineColor": "#637bb6",
            "type": "smoothedLine",
            "valueField": "buying_price"
        }],
        "chartScrollbar": {
            "graph":"g1",
            "gridAlpha":0,
            "color":"#888888",
            "scrollbarHeight":55,
            "backgroundAlpha":0,
            "selectedBackgroundAlpha":0.1,
            "selectedBackgroundColor":"#888888",
            "graphFillAlpha":0,
            "autoGridCount":true,
            "selectedGraphFillAlpha":0,
            "graphLineAlpha":0.2,
            "graphLineColor":"#c2c2c2",
            "selectedGraphLineColor":"#888888",
            "selectedGraphLineAlpha":1

        },
        "chartCursor": {
            "categoryBalloonDateFormat": "YYYY-MM-DD",
            "cursorAlpha": 0,
            "valueLineEnabled":true,
            "valueLineBalloonEnabled":true,
            "valueLineAlpha":0.5,
            "fullWidth":true
        },
        "dataDateFormat": "YYYY-MM-DD",
        "categoryField": "date",
        "categoryAxis": {
            "minPeriod": "YYYY-MM-DD",
            "parseDates": false,
            "minorGridAlpha": 0.1,
            "minorGridEnabled": false
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
