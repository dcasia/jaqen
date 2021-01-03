import { http } from 'jaqen-js'

export interface ResourceIndexInterface {
    total: number,
    from: number,
    to: number,
    currentPage: number,
    lastPage: number,
    resources: Array<{
        key: number,
        fields: ResourceCreateSchemaInterface[]
    }>
}

export interface FiltersResponseInterface {
    uriKey: string,
    fields: ResourceCreateSchemaInterface[]
}

export interface ResourceCreateSchemaInterface {
    label: string
    attribute: string
    value: string | null
    component: string
    additionalInformation: any
}

export interface ResourceCreateSchemaInterface {
    label: string
    attribute: string
    value: string | null
    component: string
    additionalInformation: any
}

export interface ResourceDetailResponse {
    key: number,
    fields: ResourceCreateSchemaInterface[]
}

export default {

    async getIndex(resource: string, filters: string, page: number = 1): Promise<ResourceIndexInterface> {

        const response = await fetch(`/jaqen-api/resource/${ resource }?fieldsFor=index-listing&filters=${ filters }&page=${ page }`, {
            headers: {
                Accept: 'application/json'
            }
        })

        if (response.ok) {

            return await response.json()

        }

        return Promise.reject(await response.json())

    },

    async deleteResource(resource: string, key: string) {

        await fetch(`/jaqen-api/resource/${ resource }`, {
            body: JSON.stringify({ _method: 'DELETE', ids: [ key ] }),
            method: 'DELETE',
            headers: {
                Accept: 'application/json'
            }
        })

    },

    async getFilters(resource: string): Promise<FiltersResponseInterface[]> {

        const response = await fetch(`/jaqen-api/resource/${ resource }/filters`, {
            headers: {
                Accept: 'application/json'
            }
        })

        return await response.json()

    },

    async getCreateFields(resource: string): Promise<ResourceCreateSchemaInterface[]> {

        const response = await fetch(`/jaqen-api/resource/${ resource }/fields?fieldsFor=creation`, {
            headers: {
                Accept: 'application/json'
            }
        })

        return await response.json()

    },

    async createResource(resource: string, body: FormData) {

        const response = await fetch(`/jaqen-api/resource/${ resource }?fieldsFor=creation`, {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json'
            }
        })

        if (response.ok) {
            return true
        }

        return Promise.reject(await response.json())

    },

    async getDetailsField(resource: string, key: string): Promise<ResourceDetailResponse> {

        const response = await fetch(`/jaqen-api/resource/${ resource }/${ key }?fieldsFor=detail`, {
            headers: {
                Accept: 'application/json'
            }
        })

        return await response.json()

    },

    async getEditFields(resource: string, key: string): Promise<ResourceDetailResponse> {

        const response = await fetch(`/jaqen-api/resource/${ resource }/${ key }?fieldsFor=creation`, {
            headers: {
                Accept: 'application/json'
            }
        })

        return await response.json()

    },

    async updateResource(resource: string, key: string, body: FormData) {

        body.append('_method', 'PATCH')

        const response = await fetch(`/jaqen-api/resource/${ resource }/${ key }?fieldsFor=creation`, {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json'
            }
        })

        if (response.ok) {
            return await response.json()
        }

        return Promise.reject(await response.json())

    },

}
