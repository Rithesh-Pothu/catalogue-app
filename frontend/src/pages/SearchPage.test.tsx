import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import App from '../App'
import { productFixture, stubBackend } from '../test-backend'

const categories = { status: 200, body: { data: ['electronics'] } }
const base = {
  '/api/categories': categories,
  '/api/featured-products': { status: 200, body: { data: [] } },
  '/api/products/3': { status: 200, body: { data: productFixture({ id: 3 }) } },
}

const item = (id: number, title: string) => productFixture({ id, title, image: `https://img.test/${id}.png` })

function renderAt(path: string) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <App />
    </MemoryRouter>,
  )
}

async function search(user: ReturnType<typeof userEvent.setup>, term: string) {
  const bar = screen.getByRole('search')
  const box = within(bar).getByRole('searchbox', { name: 'Search products' })
  await user.clear(box)
  if (term) await user.type(box, term)
  await user.click(within(bar).getByRole('button', { name: 'Search' }))
}

describe('Search', () => {
  it.each([
    ['the home page', '/'],
    ['a product page', '/products/3'],
    ['a category page', '/category/electronics'],
    ['an unknown page', '/nowhere'],
  ])('has the search bar and button on %s', async (_name, path) => {
    stubBackend({
      ...base,
      '/api/categories/electronics/products': { status: 200, body: { data: [] } },
    })

    renderAt(path)

    expect(await screen.findByRole('search')).toBeInTheDocument()
    expect(screen.getByRole('searchbox', { name: 'Search products' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Search' })).toBeInTheDocument()
  })

  it('shows the products the backend finds for the term', async () => {
    const user = userEvent.setup()
    const fetchMock = stubBackend({
      ...base,
      '/api/search?q=cotton': { status: 200, body: { data: [item(1, 'Cotton Jacket'), item(2, 'Cotton Dress')] } },
    })

    renderAt('/')
    await user.type(await screen.findByRole('searchbox', { name: 'Search products' }), 'cotton')
    await user.click(screen.getByRole('button', { name: 'Search' }))

    expect(await screen.findByRole('heading', { level: 1, name: 'Results for "cotton"' })).toBeInTheDocument()
    const tiles = screen.getAllByRole('article')
    expect(tiles.map((tile) => within(tile).getByRole('heading', { level: 3 }).textContent)).toEqual([
      'Cotton Jacket',
      'Cotton Dress',
    ])
    expect(within(tiles[0]).getByRole('link')).toHaveAttribute('href', '/products/1')
    expect(fetchMock.mock.calls.map(([url]) => url)).toContain('/api/search?q=cotton')
  })

  it('sends the term as the visitor typed it, safely encoded', async () => {
    const user = userEvent.setup()
    stubBackend({
      ...base,
      '/api/search?q=100%25%20%26%20co%2Fol%3F': { status: 200, body: { data: [item(9, 'Odd one')] } },
    })

    renderAt('/')
    await user.type(await screen.findByRole('searchbox', { name: 'Search products' }), '100% & co/ol?')
    await user.click(screen.getByRole('button', { name: 'Search' }))

    expect(await screen.findByRole('heading', { level: 3, name: 'Odd one' })).toBeInTheDocument()
  })

  it('keeps the term in the search box on the results page', async () => {
    stubBackend({ ...base, '/api/search?q=cotton': { status: 200, body: { data: [item(1, 'Cotton Jacket')] } } })

    renderAt('/search?q=cotton')

    await screen.findByRole('heading', { level: 3, name: 'Cotton Jacket' })
    expect(screen.getByRole('searchbox', { name: 'Search products' })).toHaveValue('cotton')
  })

  it('says nothing was found when there are no matches', async () => {
    const user = userEvent.setup()
    stubBackend({ ...base, '/api/search?q=submarine': { status: 200, body: { data: [] } } })

    renderAt('/')
    await screen.findByRole('search')
    await search(user, 'submarine')

    expect(await screen.findByText('Nothing found for "submarine".')).toBeInTheDocument()
    expect(screen.queryByRole('article')).not.toBeInTheDocument()
  })

  it('shows a prompt and no products for a blank term, without asking the backend', async () => {
    const user = userEvent.setup()
    const fetchMock = stubBackend(base)

    renderAt('/')
    await screen.findByRole('search')
    await search(user, '   ')

    expect(await screen.findByText('Enter a search term to find products.')).toBeInTheDocument()
    expect(screen.queryByRole('article')).not.toBeInTheDocument()
    expect(screen.queryByText(/nothing found/i)).not.toBeInTheDocument()
    expect(fetchMock.mock.calls.some(([url]) => String(url).startsWith('/api/search'))).toBe(false)
  })

  it('shows the same prompt when the search address has no term at all', async () => {
    stubBackend(base)

    renderAt('/search')

    expect(await screen.findByText('Enter a search term to find products.')).toBeInTheDocument()
  })

  it('says products are unavailable, and recovers on retry', async () => {
    const user = userEvent.setup()
    stubBackend({
      ...base,
      '/api/search?q=jacket': { status: 503, body: { message: 'Products are unavailable right now.' } },
    })

    renderAt('/search?q=jacket')
    expect(await screen.findByText('Products are unavailable right now.')).toBeInTheDocument()

    stubBackend({ ...base, '/api/search?q=jacket': { status: 200, body: { data: [item(1, 'Jacket')] } } })
    await user.click(screen.getByRole('button', { name: 'Try again' }))

    expect(await screen.findByRole('heading', { level: 3, name: 'Jacket' })).toBeInTheDocument()
  })
})
