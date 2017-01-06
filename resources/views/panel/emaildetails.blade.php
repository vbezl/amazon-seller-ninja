<div class="row">
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Email details</h3>
            </div>
            <!-- /.box-header -->

            <!-- form start -->
            <div class="box-body">

                To: {{ $email->email_to }}<br>
                Subject: {{ $email->subject }}<br>
                Sent at: {{ $email->sent_at }}<br>
                Order id: {{ $order->amazon_order_id or 'TEST'  }}<br>
                <br>
                {!! $email->body !!}
            </div>
            <!-- /.box-body -->

        </div>
        <!-- /.box -->
    </div>
</div>
