import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
export const checkout = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkout.url(options),
    method: 'get',
})

checkout.definition = {
    methods: ["get","head"],
    url: '/checkout',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
checkout.url = (options?: RouteQueryOptions) => {
    return checkout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
checkout.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkout.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
checkout.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: checkout.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
    const checkoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: checkout.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
        checkoutForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkout.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\OrderController::checkout
 * @see app/Http/Controllers/OrderController.php:16
 * @route '/checkout'
 */
        checkoutForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkout.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    checkout.form = checkoutForm
/**
* @see \App\Http\Controllers\OrderController::place
 * @see app/Http/Controllers/OrderController.php:30
 * @route '/orders'
 */
export const place = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: place.url(options),
    method: 'post',
})

place.definition = {
    methods: ["post"],
    url: '/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\OrderController::place
 * @see app/Http/Controllers/OrderController.php:30
 * @route '/orders'
 */
place.url = (options?: RouteQueryOptions) => {
    return place.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrderController::place
 * @see app/Http/Controllers/OrderController.php:30
 * @route '/orders'
 */
place.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: place.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\OrderController::place
 * @see app/Http/Controllers/OrderController.php:30
 * @route '/orders'
 */
    const placeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: place.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\OrderController::place
 * @see app/Http/Controllers/OrderController.php:30
 * @route '/orders'
 */
        placeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: place.url(options),
            method: 'post',
        })
    
    place.form = placeForm
/**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
export const success = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/orders/success/{orderNo}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
success.url = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { orderNo: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    orderNo: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        orderNo: args.orderNo,
                }

    return success.definition.url
            .replace('{orderNo}', parsedArgs.orderNo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
success.get = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
success.head = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
    const successForm = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: success.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
        successForm.get = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\OrderController::success
 * @see app/Http/Controllers/OrderController.php:58
 * @route '/orders/success/{orderNo}'
 */
        successForm.head = (args: { orderNo: string | number } | [orderNo: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    success.form = successForm
/**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\OrderController::index
 * @see app/Http/Controllers/OrderController.php:71
 * @route '/orders'
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
const OrderController = { checkout, place, success, index }

export default OrderController