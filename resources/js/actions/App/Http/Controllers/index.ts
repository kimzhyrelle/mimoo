import OrderController from './OrderController'
import Auth from './Auth'
import ProductController from './ProductController'
import SellerAnalyticsController from './SellerAnalyticsController'
import Settings from './Settings'
import Admin from './Admin'
const Controllers = {
    OrderController: Object.assign(OrderController, OrderController),
Auth: Object.assign(Auth, Auth),
ProductController: Object.assign(ProductController, ProductController),
SellerAnalyticsController: Object.assign(SellerAnalyticsController, SellerAnalyticsController),
Settings: Object.assign(Settings, Settings),
Admin: Object.assign(Admin, Admin),
}

export default Controllers