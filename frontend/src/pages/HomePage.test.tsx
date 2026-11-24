import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import App from '../App'
import { productFixture, stubBackend } from '../test-backend'

function renderHome() {
  return render(
    <MemoryRouter initialEntries={['/']}>
      <App />
    </MemoryRouter>,
  )
}

describe('Home page', () => {
  it('covers the page with a loader until the products arrive', async () => {
    let reply!: () => void
    vi.stubGlobal(
      'fetch',
      (url: string) =>
        new Promise<Response>((resolve) => {
          const body = url === '/api/categories' ? { data: [] } : { data: [productFixture({ title: 'Gold Bracelet' })] }
          const respond = () =>
            resolve(new Response(JSON.stringify(body), { status: 200, headers: { 'Content-Type': 'application/json' } }))
          if (url === '/api/featured-products') reply = respond
          else respond()
        }),
    )

    renderHome()

    expect(screen.getByRole('status', { name: 'Loading products' })).toBeInTheDocument()

    reply()
    expect(await screen.findByRole('heading', { name: 'Gold Bracelet' })).toBeInTheDocument()
    expect(screen.queryByRole('status', { name: 'Loading products' })).not.toBeInTheDocument()
  })

  it('shows the featured products', async () => {
    stubBackend({
      '/api/featured-products': {
        status: 200,
        body: {
          data: [
            // Even if a rating arrived, a featured product tile must not show it.
            productFixture({ id: 3, title: 'Mens Cotton Jacket', price: 55.99, rating: 4.5, featured: true }),
            productFixture({ id: 5, title: 'Gold Bracelet', price: 695, category: 'jewelery', featured: true }),
          ],
        },
      },
      '/api/categories': { status: 200, body: { data: ["men's clothing", 'jewelery'] } },
    })

    renderHome()

    expect(await screen.findByRole('heading', { name: 'Mens Cotton Jacket' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: 'Gold Bracelet' })).toBeInTheDocument()
    expect(screen.getAllByText('Featured')).toHaveLength(2)
    expect(screen.getByText('$55.99')).toBeInTheDocument()
    expect(screen.getByText('$695.00')).toBeInTheDocument()
    expect(screen.getByRole('img', { name: 'Mens Cotton Jacket' })).toHaveAttribute(
      'src',
      'https://img.test/1.png',
    )
    expect(screen.queryByText(/4\.5/)).not.toBeInTheDocument()
    expect(screen.queryByText(/rating/i)).not.toBeInTheDocument()
  })

  it('shows the category links in the header and on the page', async () => {
    stubBackend({
      '/api/featured-products': { status: 200, body: { data: [productFixture()] } },
      '/api/categories': { status: 200, body: { data: ["men's clothing", 'jewelery'] } },
    })

    renderHome()

    const header = await screen.findByRole('banner')
    const headerLink = await within(header).findByRole('link', { name: "men's clothing" })
    expect(headerLink).toHaveAttribute('href', "/category/men's%20clothing")
    expect(within(header).getByRole('link', { name: 'jewelery' })).toBeInTheDocument()

    const page = screen.getByRole('main')
    expect(await within(page).findByRole('link', { name: "men's clothing" })).toHaveAttribute(
      'href',
      "/category/men's%20clothing",
    )
    expect(within(page).getByRole('link', { name: 'jewelery' })).toBeInTheDocument()
  })

  it('says products are unavailable and offers a retry when the backend cannot load them', async () => {
    const user = userEvent.setup()
    stubBackend({
      '/api/featured-products': {
        status: 503,
        body: { message: 'Products are unavailable right now.' },
      },
      '/api/categories': { status: 503, body: { message: 'Products are unavailable right now.' } },
    })

    renderHome()

    expect(await screen.findByText('Products are unavailable right now.')).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Try again' })).toBeInTheDocument()

    // The source comes back, and the visitor retries.
    stubBackend({
      '/api/featured-products': { status: 200, body: { data: [productFixture()] } },
      '/api/categories': { status: 200, body: { data: ["men's clothing"] } },
    })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(await screen.findByRole('heading', { name: 'Mens Cotton Jacket' })).toBeInTheDocument()
    expect(screen.queryByText('Products are unavailable right now.')).not.toBeInTheDocument()
  })

  it('also says products are unavailable when the backend cannot be reached at all', async () => {
    stubBackend({
      '/api/featured-products': 'network-error',
      '/api/categories': 'network-error',
    })

    renderHome()

    expect(await screen.findByText('Products are unavailable right now.')).toBeInTheDocument()
  })
})
