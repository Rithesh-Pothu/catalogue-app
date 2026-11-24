import { vi } from 'vitest'

// Test helper: stands in for our backend at the network boundary by stubbing
// `fetch`. Each path maps to a status and a JSON body, or to `'network-error'`
// to simulate the backend being unreachable.
type Reply = { status: number; body: unknown } | 'network-error'

export function stubBackend(replies: Record<string, Reply>) {
  const fetchMock = vi.fn(async (url: string, _init?: RequestInit) => {
    const reply = replies[url]
    if (reply === undefined) {
      throw new Error(`Unexpected request to ${url}`)
    }
    if (reply === 'network-error') {
      throw new TypeError('network down')
    }
    return new Response(JSON.stringify(reply.body), {
      status: reply.status,
      headers: { 'Content-Type': 'application/json' },
    })
  })
  vi.stubGlobal('fetch', fetchMock)
  return fetchMock
}

export const productFixture = (overrides: Partial<Record<string, unknown>> = {}) => ({
  id: 1,
  title: 'Mens Cotton Jacket',
  price: 55.99,
  description: 'Great outerwear jackets',
  category: "men's clothing",
  image: 'https://img.test/1.png',
  rating: null,
  ...overrides,
})
