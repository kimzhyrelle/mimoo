import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:12
 * @route '/seller'
 */
export const landing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landing.url(options),
    method: 'get',
})

landing.definition = {
    methods: ["get","head"],
    url: '/seller',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:12
 * @route '/seller'
 */
landing.url = (options?: RouteQueryOptions) => {
    return landing.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:12
 * @route '/seller'
 */
landing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landing.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:12
 * @route '/seller'
 */
landing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: landing.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:12
 * @route '/seller'
 */
    const landingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: landing.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:12
 * @route '/seller'
 */
        landingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landing.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:12
 * @route '/seller'
 */
        landingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landing.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    landing.form = landingForm
/**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/seller/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:41
 * @route '/seller/dashboard'
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
 * @see routes/web.php:78
 * @route '/seller/products'
 */
export const products = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})

products.definition = {
    methods: ["get","head"],
    url: '/seller/products',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
products.url = (options?: RouteQueryOptions) => {
    return products.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
products.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
products.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: products.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
    const productsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: products.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
        productsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:78
 * @route '/seller/products'
 */
        productsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    products.form = productsForm
/**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/seller/settings',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settings.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
        settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:101
 * @route '/seller/settings'
 */
        settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settings.form = settingsForm
/**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
export const account = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})

account.definition = {
    methods: ["get","head"],
    url: '/seller/account',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
account.url = (options?: RouteQueryOptions) => {
    return account.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
account.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: account.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
account.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: account.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
    const accountForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: account.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
        accountForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: account.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:126
 * @route '/seller/account'
 */
        accountForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: account.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    account.form = accountForm
const seller = {
    landing: Object.assign(landing, landing),
dashboard: Object.assign(dashboard, dashboard),
products: Object.assign(products, products),
settings: Object.assign(settings, settings),
account: Object.assign(account, account),
}

export default seller