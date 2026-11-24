import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import App from '../App'
import { productFixture, stubBackend } from '../test-backend'

const product = productFixture({ id: 3, title: 'WD 2TB Hard Drive', category: 'electronics' })

const pageReplies = {
  '/api/categories': { status: 200, body: { data: ['electronics'] } },
  '/api/products/3': { status: 200, body: { data: product } },
}

async function openEnquiryForm() {
  const user = userEvent.setup()
  render(
    <MemoryRouter initialEntries={['/products/3']}>
      <App />
    </MemoryRouter>,
  )
  await user.click(await screen.findByRole('button', { name: 'Enquire' }))
  return user
}

async function fillIn(user: ReturnType<typeof userEvent.setup>, values: { name: string; email: string; message: string }) {
  await user.type(screen.getByLabelText('Name'), values.name)
  await user.type(screen.getByLabelText('Email address'), values.email)
  await user.type(screen.getByLabelText('Message'), values.message)
}

const valid = { name: 'Priya Shah', email: 'priya@example.com', message: 'Is this in stock?' }

describe('Enquiry form', () => {
  it('opens a form for the product when Enquire is clicked', async () => {
    stubBackend(pageReplies)

    await openEnquiryForm()

    expect(screen.getByRole('form', { name: 'Enquire about WD 2TB Hard Drive' })).toBeInTheDocument()
    expect(screen.getByLabelText('Name')).toBeInTheDocument()
    expect(screen.getByLabelText('Email address')).toBeInTheDocument()
    expect(screen.getByLabelText('Message')).toBeInTheDocument()
  })

  it('sends the enquiry to our backend and says thank you', async () => {
    const fetchMock = stubBackend({
      ...pageReplies,
      '/api/products/3/enquiries': { status: 201, body: { message: 'Thank you for your enquiry.' } },
    })
    const user = await openEnquiryForm()

    await fillIn(user, valid)
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    expect(await screen.findByText('Thank you for your enquiry.')).toBeInTheDocument()
    expect(screen.queryByLabelText('Name')).not.toBeInTheDocument()
    const [, init] = fetchMock.mock.calls.find(([url]) => url === '/api/products/3/enquiries')!
    expect(init?.method).toBe('POST')
    expect(JSON.parse(init?.body as string)).toEqual(valid)
  })

  it('shows sending progress and blocks a second submit while waiting', async () => {
    const base = stubBackend(pageReplies)
    let reply!: () => void
    const post = vi.fn(
      (_url: string, _init?: RequestInit) =>
        new Promise<Response>((resolve) => {
          reply = () => resolve(new Response(JSON.stringify({ message: 'ok' }), { status: 201 }))
        }),
    )
    vi.stubGlobal('fetch', (url: string, init?: RequestInit) =>
      url === '/api/products/3/enquiries' ? post(url, init) : base(url),
    )
    const user = await openEnquiryForm()

    await fillIn(user, valid)
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    const sending = await screen.findByRole('button', { name: 'Sending…' })
    expect(sending).toBeDisabled()
    await user.click(sending)
    expect(post).toHaveBeenCalledTimes(1)

    reply()
    expect(await screen.findByText('Thank you for your enquiry.')).toBeInTheDocument()
  })

  it('shows the backend field errors and keeps what was typed', async () => {
    stubBackend({
      ...pageReplies,
      '/api/products/3/enquiries': {
        status: 422,
        body: {
          message: 'The email field must be a valid email address.',
          errors: { email: ['The email field must be a valid email address.'] },
        },
      },
    })
    const user = await openEnquiryForm()

    await fillIn(user, { ...valid, email: 'not-an-email' })
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    expect(await screen.findByText('The email field must be a valid email address.')).toBeInTheDocument()
    expect(screen.getByLabelText('Email address')).toHaveAttribute('aria-invalid', 'true')
    expect(screen.getByLabelText('Name')).toHaveValue('Priya Shah')
    expect(screen.getByLabelText('Email address')).toHaveValue('not-an-email')
    expect(screen.getByLabelText('Message')).toHaveValue('Is this in stock?')
    expect(screen.queryByText('Thank you for your enquiry.')).not.toBeInTheDocument()
  })

  it('shows an error, never a thank you, when the email could not be sent', async () => {
    stubBackend({
      ...pageReplies,
      '/api/products/3/enquiries': {
        status: 502,
        body: { message: "We couldn't send your enquiry. Please try again." },
      },
    })
    const user = await openEnquiryForm()

    await fillIn(user, valid)
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    expect(await screen.findByRole('alert')).toHaveTextContent("We couldn't send your enquiry. Please try again.")
    expect(screen.queryByText('Thank you for your enquiry.')).not.toBeInTheDocument()
    expect(screen.getByLabelText('Message')).toHaveValue('Is this in stock?')
    expect(screen.getByRole('button', { name: 'Send enquiry' })).toBeEnabled()
  })

  it('shows an error, not silence, when the backend rejects the enquiry without saying which field', async () => {
    stubBackend({
      ...pageReplies,
      '/api/products/3/enquiries': { status: 422, body: { message: 'Invalid.' } },
    })
    const user = await openEnquiryForm()

    await fillIn(user, valid)
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    expect(await screen.findByRole('alert')).toHaveTextContent("We couldn't send your enquiry. Please try again.")
    expect(screen.queryByText('Thank you for your enquiry.')).not.toBeInTheDocument()
  })

  it('shows the same error when the backend cannot be reached', async () => {
    stubBackend({ ...pageReplies, '/api/products/3/enquiries': 'network-error' })
    const user = await openEnquiryForm()

    await fillIn(user, valid)
    await user.click(screen.getByRole('button', { name: 'Send enquiry' }))

    expect(await screen.findByRole('alert')).toHaveTextContent("We couldn't send your enquiry. Please try again.")
    expect(screen.queryByText('Thank you for your enquiry.')).not.toBeInTheDocument()
  })
})
