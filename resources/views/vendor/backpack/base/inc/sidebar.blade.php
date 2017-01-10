@if (Auth::check())
    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar">
      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <div class="pull-left image">
            <img src="http://placehold.it/160x160/00a65a/ffffff/&text={{ Auth::user()->name[0] }}" class="img-circle" alt="User Image">
          </div>
          <div class="pull-left info">
            <p>{{ Auth::user()->name }}</p>
            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
          </div>
        </div>
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
          <li class="header">{{ trans('backpack::base.administration') }}</li>
          <!-- ================================================ -->
          <!-- ==== Recommended place for admin menu items ==== -->
          <!-- ================================================ -->
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel').'/dashboard') }}"><i class="fa fa-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/products') }}"><i class="fa fa-tag"></i> <span>Products</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/templates') }}"><i class="fa fa-tag"></i> <span>Templates</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/emails') }}"><i class="fa fa-tag"></i> <span>Sent emails</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/unsubscribers') }}"><i class="fa fa-tag"></i> <span>Unsubscribers</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/feedbacks') }}"><i class="fa fa-tag"></i> <span>Feedbacks</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/connect') }}"><i class="fa fa-cog"></i> <span>Amazon connection</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel') . '/setting') }}"><i class="fa fa-cog"></i> <span>Settings</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix').'/page') }}"><i class="fa fa-file-o"></i> <span>Pages</span></a></li>

          <!-- ======================================= -->
          <li class="header">{{ trans('backpack::base.user') }}</li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'panel').'/logout') }}"><i class="fa fa-sign-out"></i> <span>{{ trans('backpack::base.logout') }}</span></a></li>
        </ul>
      </section>
      <!-- /.sidebar -->
    </aside>
@endif
