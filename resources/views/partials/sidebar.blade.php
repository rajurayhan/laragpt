 <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">LHG SOW Tool</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ca.slack-edge.com/T08KLGRSQ-U016C8R8486-d153c69dce68-512" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Raju Rayhan</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          {{-- <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Starter Pages
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Active Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inactive Page</p>
                </a>
              </li>
            </ul>
          </li> --}}
          {{-- <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Simple Link
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li> --}}
          {{-- <li class="nav-item">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li> --}}
          <li class="nav-header">MY SPACES</li>
          <x-sidebar-item :url="route('home')" :icon="'fa-th'" :active="request()->routeIs('home')">
            SOW Generator
          </x-sidebar-item>
          <x-sidebar-item :url="'#'" :icon="'fa-copy'" :active="request()->routeIs('dashboard')">
            SOWs
          </x-sidebar-item>
          <x-sidebar-item :url="'#'" :icon="'fa-file'" :active="request()->routeIs('home')">
            Documents
          </x-sidebar-item>
          <x-sidebar-item :url="'#'" :icon="'fa-image'" :active="request()->routeIs('home')">
            Images
          </x-sidebar-item>

          <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <a href="#" class="nav-link " onclick="event.preventDefault();
              this.closest('form').submit();">
                  <i class="nav-icon  "></i>
                  <p>
                    Logout
                  </p>
              </a>
            </form>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
