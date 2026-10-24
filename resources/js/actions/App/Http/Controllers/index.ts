import SellerDashboardController from './SellerDashboardController'
import Auth from './Auth'
import OrderController from './OrderController'
import ProductController from './ProductController'
import CartController from './CartController'
import SellerAnalyticsController from './SellerAnalyticsController'
import SellerOrderController from './SellerOrderController'
import SellerDashboardStreamController from './SellerDashboardStreamController'
import Api from './Api'
import Settings from './Settings'
import Admin from './Admin'
const Controllers = {
    SellerDashboardController: Object.assign(SellerDashboardController, SellerDashboardController),
Auth: Object.assign(Auth, Auth),
OrderController: Object.assign(OrderController, OrderController),
ProductController: Object.assign(ProductController, ProductController),
CartController: Object.assign(CartController, CartController),
SellerAnalyticsController: Object.assign(SellerAnalyticsController, SellerAnalyticsController),
SellerOrderController: Object.assign(SellerOrderController, SellerOrderController),
SellerDashboardStreamController: Object.assign(SellerDashboardStreamController, SellerDashboardStreamController),
Api: Object.assign(Api, Api),
Settings: Object.assign(Settings, Settings),
Admin: Object.assign(Admin, Admin),
}

export default Controllers