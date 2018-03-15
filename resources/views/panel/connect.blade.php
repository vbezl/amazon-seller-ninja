@extends('backpack::layout')

@section('header')
<section class="content-header">
    <h1>
        Amazon Connection Settings<small>Configure connection to your Amazon seller account</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'panel')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">Amazon connection</li>
    </ol>
</section>
@endsection


@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Amazon connection settings</h3>
            </div>
            <!-- /.box-header -->

            <!-- form start -->
            <form role="form" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="box-body">
                    <div class="form-group">
                        <label for="InputMarketplace">Marketplace</label>
                        {!! Form::select('marketplace_id', $marketplaces, $user->marketplace_id ?: 3, ['class' => 'form-control', 'id' => 'InputMarketplace']) !!}
                    </div>
                    <div class="form-group">
                        <label for="InputSellerID">Seller ID</label>
                        <input type="text" class="form-control" id="InputSellerID" name="amazon_seller_id" placeholder="Enter Amazon Seller ID" value="{{ $user->amazon_seller_id }}">
                        <small id="passwordHelpInline" class="text-muted">
                            Must be 12-16 characters long.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Amazon MWS Auth Token</label>
                        <input type="text" class="form-control" id="exampleInputPassword1" name="amazon_mws_token" placeholder="Enter Amazon MWS Auth Token" value="{{ $user->amazon_mws_token }}">
                    </div>
                    <div class="form-group">
                        <label for="InputEmailFrom">Amazon Email (login)</label>
                        <input type="email" class="form-control" id="InputEmailFrom" name="amazon_email_from" placeholder="Enter Amazon Email login" value="{{ $user->amazon_email_from }}">
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        <!-- /.box -->
    </div>
</div>
@endsection
