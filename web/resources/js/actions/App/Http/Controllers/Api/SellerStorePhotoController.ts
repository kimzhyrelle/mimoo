import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:17
 * @route '/api/seller/profile-photo'
 */
export const uploadProfilePhoto = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadProfilePhoto.url(options),
    method: 'post',
})

uploadProfilePhoto.definition = {
    methods: ["post"],
    url: '/api/seller/profile-photo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:17
 * @route '/api/seller/profile-photo'
 */
uploadProfilePhoto.url = (options?: RouteQueryOptions) => {
    return uploadProfilePhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:17
 * @route '/api/seller/profile-photo'
 */
uploadProfilePhoto.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadProfilePhoto.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:17
 * @route '/api/seller/profile-photo'
 */
    const uploadProfilePhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: uploadProfilePhoto.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:17
 * @route '/api/seller/profile-photo'
 */
        uploadProfilePhotoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: uploadProfilePhoto.url(options),
            method: 'post',
        })
    
    uploadProfilePhoto.form = uploadProfilePhotoForm
/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:70
 * @route '/api/seller/cover-photo'
 */
export const uploadCoverPhoto = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadCoverPhoto.url(options),
    method: 'post',
})

uploadCoverPhoto.definition = {
    methods: ["post"],
    url: '/api/seller/cover-photo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:70
 * @route '/api/seller/cover-photo'
 */
uploadCoverPhoto.url = (options?: RouteQueryOptions) => {
    return uploadCoverPhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:70
 * @route '/api/seller/cover-photo'
 */
uploadCoverPhoto.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadCoverPhoto.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:70
 * @route '/api/seller/cover-photo'
 */
    const uploadCoverPhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: uploadCoverPhoto.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::uploadCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:70
 * @route '/api/seller/cover-photo'
 */
        uploadCoverPhotoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: uploadCoverPhoto.url(options),
            method: 'post',
        })
    
    uploadCoverPhoto.form = uploadCoverPhotoForm
/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:123
 * @route '/api/seller/profile-photo'
 */
export const deleteProfilePhoto = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteProfilePhoto.url(options),
    method: 'delete',
})

deleteProfilePhoto.definition = {
    methods: ["delete"],
    url: '/api/seller/profile-photo',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:123
 * @route '/api/seller/profile-photo'
 */
deleteProfilePhoto.url = (options?: RouteQueryOptions) => {
    return deleteProfilePhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:123
 * @route '/api/seller/profile-photo'
 */
deleteProfilePhoto.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteProfilePhoto.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:123
 * @route '/api/seller/profile-photo'
 */
    const deleteProfilePhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteProfilePhoto.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteProfilePhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:123
 * @route '/api/seller/profile-photo'
 */
        deleteProfilePhotoForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteProfilePhoto.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteProfilePhoto.form = deleteProfilePhotoForm
/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:150
 * @route '/api/seller/cover-photo'
 */
export const deleteCoverPhoto = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteCoverPhoto.url(options),
    method: 'delete',
})

deleteCoverPhoto.definition = {
    methods: ["delete"],
    url: '/api/seller/cover-photo',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:150
 * @route '/api/seller/cover-photo'
 */
deleteCoverPhoto.url = (options?: RouteQueryOptions) => {
    return deleteCoverPhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:150
 * @route '/api/seller/cover-photo'
 */
deleteCoverPhoto.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteCoverPhoto.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:150
 * @route '/api/seller/cover-photo'
 */
    const deleteCoverPhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteCoverPhoto.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\SellerStorePhotoController::deleteCoverPhoto
 * @see app/Http/Controllers/Api/SellerStorePhotoController.php:150
 * @route '/api/seller/cover-photo'
 */
        deleteCoverPhotoForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteCoverPhoto.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteCoverPhoto.form = deleteCoverPhotoForm
const SellerStorePhotoController = { uploadProfilePhoto, uploadCoverPhoto, deleteProfilePhoto, deleteCoverPhoto }

export default SellerStorePhotoController