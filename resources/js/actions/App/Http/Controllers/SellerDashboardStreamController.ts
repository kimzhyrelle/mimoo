import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
export const stream = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stream.url(options),
    method: 'get',
})

stream.definition = {
    methods: ["get","head"],
    url: '/api/seller/dashboard/stream',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
stream.url = (options?: RouteQueryOptions) => {
    return stream.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
stream.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stream.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
stream.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stream.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
    const streamForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: stream.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
        streamForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stream.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SellerDashboardStreamController::stream
 * @see app/Http/Controllers/SellerDashboardStreamController.php:18
 * @route '/api/seller/dashboard/stream'
 */
        streamForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: stream.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    stream.form = streamForm
const SellerDashboardStreamController = { stream }

export default SellerDashboardStreamController