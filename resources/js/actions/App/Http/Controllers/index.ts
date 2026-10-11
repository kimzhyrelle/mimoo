import OrderController from './OrderController'
import Auth from './Auth'
import Settings from './Settings'
import Admin from './Admin'
const Controllers = {
    OrderController: Object.assign(OrderController, OrderController),
Auth: Object.assign(Auth, Auth),
Settings: Object.assign(Settings, Settings),
Admin: Object.assign(Admin, Admin),
}

export default Controllers