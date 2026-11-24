import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import App from '../App'
import { categoryPath } from '../format'
import { productFixture, stubBackend } from '../test-backend'

const categories = { status: 200, body: { data: ["men's clothing", 'electronics'] } }

const item = (id: number, title: string, category = "men's clothing") =>
  productFixture({ id, title, category, image: `https://img.test/${id}.png` })

function renderAt(path: string) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <App />
    </MemoryRouter>,
  )
}

describe('Category page', () => {
  it('lists the products the backend returns, in the order it returns them', async () => {
    stubBackend({
      '/api/categories': categories,
      "/api/categories/men's%20clothing/products": {
        status: 200,
        body: { data: [item(5, 'Featured Jacket'), item(1, 'Backpack'), item(3, 'T-shirt')] },
      },
    })

    renderAt("/category/men's%20clothing")

    expect(await screen.findByRole('heading', { level: 1, name: "men's clothing" })).toBeInTheDocument()
    const tiles = screen.getAllByRole('article')
    expect(tiles.map((tile) => within(tile).getByRole('heading', { level: 3 }).textContent)).toEqual([
      'Featured Jacket',
      'Backpack',
      'T-shirt',
    ])
    expect(within(tiles[0]).getByRole('link')).toHaveAttribute('href', '/products/5')
  })

  it('opens from a category link on the home page', async () => {
    const user = userEvent.setup()
    stubBackend({
      '/api/categories': categories,
      '/api/featured-products': { status: 200, body: { data: [] } },
      '/api/categories/electronics/products': {
        status: 200,
        body: { data: [item(4, 'Hard drive', 'electronics')] },
      },
    })

    renderAt('/')
    const browse = (await screen.findByRole('heading', { name: 'Browse by category' })).closest('section')!
    await user.click(within(browse).getByRole('link', { name: 'electronics' }))

    expect(await screen.findByRole('heading', { level: 1, name: 'electronics' })).toBeInTheDocument()
    expect(screen.getByRole('heading', { level: 3, name: 'Hard drive' })).toBeInTheDocument()
  })

  it('finds a category whose name has reserved characters, from its link to the backend request', async () => {
    const name = 'audio/video? 100% #1'
    stubBackend({
      '/api/categories': { status: 200, body: { data: [name] } },
      [`/api/categories/${encodeURIComponent(name)}/products`]: {
        status: 200,
        body: { data: [item(7, 'Speaker', name)] },
      },
    })

    renderAt(categoryPath(name))

    expect(await screen.findByRole('heading', { level: 1, name })).toBeInTheDocument()
    expect(screen.getByRole('heading', { level: 3, name: 'Speaker' })).toBeInTheDocument()
  })

  it('shows a not-found page for a category that does not exist', async () => {
    stubBackend({
      '/api/categories': categories,
      '/api/categories/toys/products': { status: 404, body: { message: 'Category not found.' } },
    })

    renderAt('/category/toys')

    expect(await screen.findByText("We couldn't find that category.")).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Back to the home page' })).toHaveAttribute('href', '/')
  })

  it('says products are unavailable, and recovers on retry', async () => {
    const user = userEvent.setup()
    stubBackend({
      '/api/categories': categories,
      '/api/categories/electronics/products': { status: 503, body: { message: 'Products are unavailable right now.' } },
    })

    renderAt('/category/electronics')
    expect(await screen.findByText('Products are unavailable right now.')).toBeInTheDocument()

    stubBackend({
      '/api/categories': categories,
      '/api/categories/electronics/products': { status: 200, body: { data: [item(4, 'Hard drive', 'electronics')] } },
    })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(await screen.findByRole('heading', { level: 3, name: 'Hard drive' })).toBeInTheDocument()
  })

  it('shows a not-found page for an address that is not a page at all', async () => {
    stubBackend({ '/api/categories': categories })

    renderAt('/no/such/page')

    expect(await screen.findByText("We couldn't find that page.")).toBeInTheDocument()
  })
})
