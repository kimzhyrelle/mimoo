import SellerDashboardController from './SellerDashboardController'
import OrderController from './OrderController'
import Auth from './Auth'
import ProductController from './ProductController'
import CartController from './CartController'
import SellerAnalyticsController from './SellerAnalyticsController'
import SellerOrderController from './SellerOrderController'
import SellerDashboardStreamController from './SellerDashboardStreamController'
import Settings from './Settings'
import Admin from './Admin'
const Controllers = {
    SellerDashboardController: Object.assign(SellerDashboardController, SellerDashboardController),
OrderController: Object.assign(OrderController, OrderController),
Auth: Object.assign(Auth, Auth),
ProductController: Object.assign(ProductController, ProductController),
CartController: Object.assign(CartController, CartController),
SellerAnalyticsController: Object.assign(SellerAnalyticsController, SellerAnalyticsController),
SellerOrderController: Object.assign(SellerOrderController, SellerOrderController),
SellerDashboardStreamController: Object.assign(SellerDashboardStreamController, SellerDashboardStreamController),
Settings: Object.assign(Settings, Settings),
Admin: Object.assign(Admin, Admin),
}

export default Controllers