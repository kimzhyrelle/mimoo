import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
 * @route '/register'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
 * @route '/register'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
 * @route '/register'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
 * @route '/register'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
 * @route '/register'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
export const seller = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seller.url(options),
    method: 'get',
})

seller.definition = {
    methods: ["get","head"],
    url: '/register/seller',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
seller.url = (options?: RouteQueryOptions) => {
    return seller.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
seller.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seller.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
seller.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: seller.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
    const sellerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: seller.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
        sellerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seller.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:87
 * @route '/register/seller'
 */
        sellerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seller.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    seller.form = sellerForm
/**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
export const logistics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})

logistics.definition = {
    methods: ["get","head"],
    url: '/register/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
logistics.url = (options?: RouteQueryOptions) => {
    return logistics.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
logistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
logistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logistics.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
    const logisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logistics.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
        logisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:91
 * @route '/register/logistics'
 */
        logisticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    logistics.form = logisticsForm
/**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
export const pending = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pending.url(options),
    method: 'get',
})

pending.definition = {
    methods: ["get","head"],
    url: '/register/pending',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
pending.url = (options?: RouteQueryOptions) => {
    return pending.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
pending.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pending.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
pending.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pending.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
    const pendingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pending.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
        pendingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pending.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:95
 * @route '/register/pending'
 */
        pendingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pending.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pending.form = pendingForm
const register = {
    store: Object.assign(store, store),
seller: Object.assign(seller, seller),
logistics: Object.assign(logistics, logistics),
pending: Object.assign(pending, pending),
}

export default register