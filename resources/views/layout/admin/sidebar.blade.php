<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{route('home')}}">
                <i class="mdi mdi-monitor-dashboard menu-icon"></i>
                <span class="menu-title">Bosh sahifa</span>
            </a>
        </li>
        <li class="nav-item nav-category">Boshqaruv</li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <i class="menu-icon mdi mdi-food"></i>
                <span class="menu-title">Maxsulotlar</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('products.index')}}">Maxsulotlar</a></li>
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('categories.index')}}">Maxsulot turi</a></li>
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('product-stock.index')}}">Kirim</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
                <i class="menu-icon mdi mdi-sofa-outline"></i>
                <span class="menu-title">Choyxona joylari</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="form-elements">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link fw-bold" href="{{route('places.index')}}">Xona / So'ri / Stol</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <i class="menu-icon mdi mdi-cart-outline"></i>
                <span class="menu-title">Savdo</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="charts">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('orders.index')}}">Buyurtmalar tarixi</a></li>
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('cafe.create')}}">Choyxona ichida</a></li>
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('orders.deleted')}}">O'chirilgan buyurtmalar</a></li>
                </ul>
            </div>
        </li>
       <li class="nav-item">
           <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
               <i class="menu-icon mdi mdi-table"></i>
               <span class="menu-title">Xarajatlar</span>
               <i class="menu-arrow"></i>
           </a>
           <div class="collapse" id="tables">
               <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('expense-categories.index')}}">Xarajat kategoriyasi</a></li>
                    <li class="nav-item"> <a class="nav-link fw-bold" href="{{route('expenses.index')}}">Xarajatlar</a></li>
               </ul>
           </div>
       </li>
{{--        <li class="nav-item">--}}
{{--            <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">--}}
{{--                <i class="menu-icon mdi mdi-layers-outline"></i>--}}
{{--                <span class="menu-title">Icons</span>--}}
{{--                <i class="menu-arrow"></i>--}}
{{--            </a>--}}
{{--            <div class="collapse" id="icons">--}}
{{--                <ul class="nav flex-column sub-menu">--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/icons/font-awesome.html">Font Awesome</a></li>--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        </li>--}}
{{--        <li class="nav-item">--}}
{{--            <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">--}}
{{--                <i class="menu-icon mdi mdi-account-circle-outline"></i>--}}
{{--                <span class="menu-title">User Pages</span>--}}
{{--                <i class="menu-arrow"></i>--}}
{{--            </a>--}}
{{--            <div class="collapse" id="auth">--}}
{{--                <ul class="nav flex-column sub-menu">--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/samples/blank-page.html"> Blank Page </a></li>--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> 404 </a></li>--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/samples/error-500.html"> 500 </a></li>--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/samples/login.html"> Login </a></li>--}}
{{--                    <li class="nav-item"> <a class="nav-link" href="pages/samples/register.html"> Register </a></li>--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        </li>--}}
{{--        <li class="nav-item">--}}
{{--            <a class="nav-link" href="docs/documentation.html">--}}
{{--                <i class="menu-icon mdi mdi-file-document"></i>--}}
{{--                <span class="menu-title">Documentation</span>--}}
{{--            </a>--}}
{{--        </li>--}}
    </ul>
</nav>
