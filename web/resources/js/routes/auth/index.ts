import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import google723582 from './google'
/**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
export const google = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: google.url(options),
    method: 'get',
})

google.definition = {
    methods: ["get","head"],
    url: '/auth/google',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
google.url = (options?: RouteQueryOptions) => {
    return google.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
google.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: google.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
google.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: google.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
    const googleForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: google.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
        googleForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: google.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\SocialiteController::google
 * @see app/Http/Controllers/Auth/SocialiteController.php:17
 * @route '/auth/google'
 */
        googleForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: google.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    google.form = googleForm
const auth = {
    google: Object.assign(google, google723582),
}

export default auth