import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
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
const orders = {
    place: Object.assign(place, place),
index: Object.assign(index, index),
}

export default orders