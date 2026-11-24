// The only place in the frontend that knows how to reach the backend.
// Every request goes to our own /api, never to a third-party product source.

export type Product = {
  id: number
  title: string
  price: number
  description: string
  category: string
  image: string
  rating: number | null
}

/** Products could not be loaded: the backend said so, or could not be reached. */
export class ProductsUnavailable extends Error {
  constructor() {
    super('Products are unavailable right now.')
  }
}

/** The backend has nothing at the address we asked for. */
export class ResourceNotFound extends Error {
  constructor() {
    super('Not found.')
  }
}

async function getData<T>(path: string): Promise<T> {
  let response: Response
  try {
    response = await fetch(path)
  } catch {
    throw new ProductsUnavailable()
  }
  if (response.status === 404) {
    throw new ResourceNotFound()
  }
  if (!response.ok) {
    throw new ProductsUnavailable()
  }
  return ((await response.json()) as { data: T }).data
}

export const getFeaturedProducts = () => getData<Product[]>('/api/featured-products')

export const getCategories = () => getData<string[]>('/api/categories')

export const getProduct = (id: string) => getData<Product>(`/api/products/${encodeURIComponent(id)}`)
