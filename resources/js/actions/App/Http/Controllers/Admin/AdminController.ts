import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminController::index
 * @see app/Http/Controllers/Admin/AdminController.php:18
 * @route '/admin'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\AdminController::verify
 * @see app/Http/Controllers/Admin/AdminController.php:33
 * @route '/admin/verify'
 */
export const verify = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/admin/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminController::verify
 * @see app/Http/Controllers/Admin/AdminController.php:33
 * @route '/admin/verify'
 */
verify.url = (options?: RouteQueryOptions) => {
    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminController::verify
 * @see app/Http/Controllers/Admin/AdminController.php:33
 * @route '/admin/verify'
 */
verify.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminController::verify
 * @see app/Http/Controllers/Admin/AdminController.php:33
 * @route '/admin/verify'
 */
    const verifyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminController::verify
 * @see app/Http/Controllers/Admin/AdminController.php:33
 * @route '/admin/verify'
 */
        verifyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(options),
            method: 'post',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminController::dashboard
 * @see app/Http/Controllers/Admin/AdminController.php:55
 * @route '/admin/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
export const manageRegistrations = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: manageRegistrations.url(options),
    method: 'get',
})

manageRegistrations.definition = {
    methods: ["get","head"],
    url: '/admin/manage-registrations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
manageRegistrations.url = (options?: RouteQueryOptions) => {
    return manageRegistrations.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
manageRegistrations.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: manageRegistrations.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
manageRegistrations.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: manageRegistrations.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
    const manageRegistrationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: manageRegistrations.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
        manageRegistrationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: manageRegistrations.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminController::manageRegistrations
 * @see app/Http/Controllers/Admin/AdminController.php:63
 * @route '/admin/manage-registrations'
 */
        manageRegistrationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: manageRegistrations.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    manageRegistrations.form = manageRegistrationsForm
/**
* @see \App\Http\Controllers\Admin\AdminController::logout
 * @see app/Http/Controllers/Admin/AdminController.php:71
 * @route '/admin/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/admin/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminController::logout
 * @see app/Http/Controllers/Admin/AdminController.php:71
 * @route '/admin/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminController::logout
 * @see app/Http/Controllers/Admin/AdminController.php:71
 * @route '/admin/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminController::logout
 * @see app/Http/Controllers/Admin/AdminController.php:71
 * @route '/admin/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminController::logout
 * @see app/Http/Controllers/Admin/AdminController.php:71
 * @route '/admin/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
const AdminController = { index, verify, dashboard, manageRegistrations, logout }

export default AdminController