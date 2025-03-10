<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="" class="logo">
                <img src="" alt="navbar brand" class="navbar-brand"
                    height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item">
                    <a href="" target="_blank"><i class="fas fa-globe"></i>
                        <p>Visit Site</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('vendor.dashboard') }}"><i class="fas fa-home"></i>
                        <p>Dashboard</p>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Components</h4>
                </li>


                <li class="nav-item ">
                    <a data-bs-toggle="collapse" href="#base">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:25px; font-size:16px;margin-right: 15px;"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path d="M326.3 218.8c0 20.5-16.7 37.2-37.2 37.2h-70.3v-74.4h70.3c20.5 0 37.2 16.7 37.2 37.2zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-128.1-37.2c0-47.9-38.9-86.8-86.8-86.8H169.2v248h49.6v-74.4h70.3c47.9 0 86.8-38.9 86.8-86.8z" />
                        </svg>

                        <p>Manage Product </p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse " id="base">
                        <ul class="nav nav-collapse">

                            <li class="">
                                <a href="{{ route('vendor.products.index') }}">
                                    <span class="sub-item">Products</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item ">
                    <a data-bs-toggle="collapse" href="#order">
                        <i class="fas fa-layer-group"></i>
                        <p>Manage Order </p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse " id="order">
                        <ul class="nav nav-collapse">
                            <li class="">
                                <a href="{{ route('vendor.order.index') }}">
                                    All Order
                                </a>
                            </li>

                            <li class=""> <a href="{{ route('vendor.order.pending') }}">Pending Orders</a></li>
                            <li class=""> <a href="{{ route('vendor.order.confirmed') }}">Confirm Orders</a></li>
                            <li class=""> <a href="{{ route('vendor.order.processing') }}">Processing Orders</a></li>
                            <li class=""> <a href="{{ route('vendor.order.delivered') }}">Delivered Orders</a></li>
                            <li class=""> <a href="{{ route('vendor.order.completed') }}">Completed Orders</a></li>
                            <li class=""> <a href="{{ route('vendor.order.cancled') }}">Canceled Orders</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item ">
                    <a data-bs-toggle="collapse" href="#payment">
                        <i class="fas fa-layer-group"></i>
                        <p>Manage Payment </p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse " id="payment">
                        <ul class="nav nav-collapse">
                            <li class="">
                                <a href="{{ route('vendor.payment.request') }}">
                                    Request payment
                                </a>
                            </li>
                            <li class=""> <a href="{{ route('vendor.payment.history') }}">Payment History</a></li>
                        </ul>
                    </div>
                </li>


                <li
                    class="nav-item ">
                    <a data-bs-toggle="collapse" href="#hompage">
                        <i class="fas fa-layer-group"></i>
                        <p>Setings</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse "
                        id="hompage">
                        <ul class="nav nav-collapse">
                            <li class="">
                                <a href="">
                                    <span class="sub-item">Generals</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
