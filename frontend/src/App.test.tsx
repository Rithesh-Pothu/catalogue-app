import { render, screen } from '@testing-library/react'
import { describe, expect, it, vi } from 'vitest'
import App from './App'

describe('App', () => {
  it('shows that the backend is running, using data fetched from our own backend', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ status: 'ok' }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      }),
    )
    vi.stubGlobal('fetch', fetchMock)

    render(<App />)

    expect(await screen.findByText(/backend: ok/i)).toBeInTheDocument()
    expect(fetchMock).toHaveBeenCalledWith('/api/health')
  })

  it('says the backend is unreachable when the request fails', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('network down')))

    render(<App />)

    expect(await screen.findByText(/backend: unreachable/i)).toBeInTheDocument()
  })
})
