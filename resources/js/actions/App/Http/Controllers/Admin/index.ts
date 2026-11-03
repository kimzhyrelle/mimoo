import VoucherController from './VoucherController'
import AdminController from './AdminController'
const Admin = {
    VoucherController: Object.assign(VoucherController, VoucherController),
AdminController: Object.assign(AdminController, AdminController),
}

export default Admin