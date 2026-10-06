import OrderController from './OrderController'
import Settings from './Settings'
import Admin from './Admin'
const Controllers = {
    OrderController: Object.assign(OrderController, OrderController),
Settings: Object.assign(Settings, Settings),
Admin: Object.assign(Admin, Admin),
}

export default Controllers