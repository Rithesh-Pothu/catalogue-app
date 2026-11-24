import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import App from '../App'
import { productFixture, stubBackend } from '../test-backend'

const categories = { status: 200, body: { data: ['electronics'] } }

const hardDrive = (overrides: Partial<Record<string, unknown>> = {}) =>
  productFixture({
    id: 3,
    title: 'WD 2TB Elements Portable External Hard Drive',
    price: 64,
    description: 'USB 3.0 and USB 2.0 compatibility',
    category: 'electronics',
    image: 'https://img.test/3.png',
    rating: 3.3,
    ...overrides,
  })

function renderAt(path: string) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <App />
    </MemoryRouter>,
  )
}

describe('Product page', () => {
  it('marks a featured product as featured, and others not', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive({ featured: true, rating: null }) } },
    })
    renderAt('/products/3')
    expect(await screen.findByText('Featured')).toBeInTheDocument()
  })

  it('shows no featured badge on an ordinary product', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive() } },
    })
    renderAt('/products/3')
    await screen.findByRole('heading', { level: 1 })
    expect(screen.queryByText('Featured')).not.toBeInTheDocument()
  })

  it('shows the product details and an Enquire button', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive() } },
    })

    renderAt('/products/3')

    expect(
      await screen.findByRole('heading', { level: 1, name: 'WD 2TB Elements Portable External Hard Drive' }),
    ).toBeInTheDocument()
    expect(screen.getByRole('img', { name: 'WD 2TB Elements Portable External Hard Drive' })).toHaveAttribute(
      'src',
      'https://img.test/3.png',
    )
    expect(screen.getByText('$64.00')).toBeInTheDocument()
    expect(screen.getByText('USB 3.0 and USB 2.0 compatibility')).toBeInTheDocument()
    const product = screen.getByRole('article')
    expect(within(product).getByRole('link', { name: 'electronics' })).toHaveAttribute(
      'href',
      '/category/electronics',
    )
    expect(screen.getByRole('button', { name: 'Enquire' })).toBeInTheDocument()
  })

  it('shows the rating of a product that has one', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive({ rating: 3.3 }) } },
    })

    renderAt('/products/3')

    expect(await screen.findByText('Rating: 3.3')).toBeInTheDocument()
  })

  it('leaves out the rating line when the product has no rating', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive({ rating: null }) } },
    })

    renderAt('/products/3')

    await screen.findByRole('heading', { level: 1 })
    expect(screen.queryByText(/rating/i)).not.toBeInTheDocument()
  })

  it('shows a not-found page for a product that does not exist', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/products/99': { status: 404, body: { message: 'Product not found.' } },
    })

    renderAt('/products/99')

    expect(await screen.findByText("We couldn't find that product.")).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Back to the home page' })).toHaveAttribute('href', '/')
    expect(screen.queryByRole('button', { name: 'Enquire' })).not.toBeInTheDocument()
  })

  it('says products are unavailable, and recovers on retry', async () => {
    const user = userEvent.setup()
    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 503, body: { message: 'Products are unavailable right now.' } },
    })

    renderAt('/products/3')

    expect(await screen.findByText('Products are unavailable right now.')).toBeInTheDocument()

    stubBackend({
      '/api/categories': categories,
      '/api/products/3': { status: 200, body: { data: hardDrive() } },
    })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(
      await screen.findByRole('heading', { level: 1, name: 'WD 2TB Elements Portable External Hard Drive' }),
    ).toBeInTheDocument()
  })

  it('opens when a visitor clicks a product tile on the home page', async () => {
    const user = userEvent.setup()
    stubBackend({
      '/api/categories': categories,
      '/api/featured-products': { status: 200, body: { data: [hardDrive({ rating: null })] } },
      '/api/products/3': { status: 200, body: { data: hardDrive({ rating: null }) } },
    })

    renderAt('/')
    await user.click(await screen.findByRole('link', { name: /WD 2TB Elements/ }))

    expect(await screen.findByRole('heading', { level: 1, name: /WD 2TB Elements/ })).toBeInTheDocument()
    expect(screen.getByText('USB 3.0 and USB 2.0 compatibility')).toBeInTheDocument()
  })
})
