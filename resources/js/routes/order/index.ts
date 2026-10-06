import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
const order = {
    success: Object.assign(success, success),
}

export default order