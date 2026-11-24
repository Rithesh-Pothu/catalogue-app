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

export type EnquiryInput = { name: string; email: string; message: string }

/** Field name → messages, as returned by the backend's validation. */
export type EnquiryErrors = Partial<Record<keyof EnquiryInput, string[]>>

/** The backend refused the enquiry because of what was typed. Nothing was sent. */
export class EnquiryRejected extends Error {
  readonly errors: EnquiryErrors

  constructor(errors: EnquiryErrors) {
    super('The enquiry was rejected.')
    this.errors = errors
  }
}

/** The enquiry could not be sent (mail failure, backend down, unknown product). */
export class EnquiryNotSent extends Error {
  constructor() {
    super("We couldn't send your enquiry. Please try again.")
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

export const getCategoryProducts = (category: string) =>
  getData<Product[]>(`/api/categories/${encodeURIComponent(category)}/products`)

export const searchProducts = (term: string) =>
  getData<Product[]>(`/api/search?q=${encodeURIComponent(term)}`)

export const getProduct = (id: string) => getData<Product>(`/api/products/${encodeURIComponent(id)}`)

export async function sendEnquiry(productId: number, input: EnquiryInput): Promise<void> {
  let response: Response
  try {
    response = await fetch(`/api/products/${productId}/enquiries`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(input),
    })
  } catch {
    throw new EnquiryNotSent()
  }
  if (response.status === 422) {
    const body = (await response.json().catch(() => ({}))) as { errors?: EnquiryErrors }
    // A rejection that names no field gives the form nothing to show: treat it as not sent.
    if (!body.errors || Object.keys(body.errors).length === 0) {
      throw new EnquiryNotSent()
    }
    throw new EnquiryRejected(body.errors)
  }
  if (!response.ok) {
    throw new EnquiryNotSent()
  }
}
