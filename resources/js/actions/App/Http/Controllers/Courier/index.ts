import IncomingShipmentsController from './IncomingShipmentsController'
import PickupManagementController from './PickupManagementController'
const Courier = {
    IncomingShipmentsController: Object.assign(IncomingShipmentsController, IncomingShipmentsController),
PickupManagementController: Object.assign(PickupManagementController, PickupManagementController),
}

export default Courier