import AuthController from './AuthController'
import ProductController from './ProductController'
import CartController from './CartController'
import BuyerOrderController from './BuyerOrderController'
import SellerStorePhotoController from './SellerStorePhotoController'
const Api = {
    AuthController: Object.assign(AuthController, AuthController),
ProductController: Object.assign(ProductController, ProductController),
CartController: Object.assign(CartController, CartController),
BuyerOrderController: Object.assign(BuyerOrderController, BuyerOrderController),
SellerStorePhotoController: Object.assign(SellerStorePhotoController, SellerStorePhotoController),
}

export default Api