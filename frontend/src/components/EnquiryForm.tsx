import { useEffect, useState, type FormEvent } from 'react'
import {
  EnquiryNotSent,
  EnquiryRejected,
  sendEnquiry,
  type EnquiryErrors,
  type EnquiryInput,
  type Product,
} from '../api/client'

type Status = 'editing' | 'sending'

const TOAST_MILLISECONDS = 6000

const fields: { name: keyof EnquiryInput; label: string }[] = [
  { name: 'name', label: 'Name' },
  { name: 'email', label: 'Email address' },
  { name: 'message', label: 'Message' },
]

export default function EnquiryForm({ product }: { product: Product }) {
  const [values, setValues] = useState<EnquiryInput>({ name: '', email: '', message: '' })
  const [status, setStatus] = useState<Status>('editing')
  const [fieldErrors, setFieldErrors] = useState<EnquiryErrors>({})
  const [failure, setFailure] = useState<string | null>(null)
  const [thanked, setThanked] = useState(false)

  useEffect(() => {
    if (!thanked) return
    const timer = setTimeout(() => setThanked(false), TOAST_MILLISECONDS)
    return () => clearTimeout(timer)
  }, [thanked])

  async function submit(event: FormEvent) {
    event.preventDefault()
    if (status === 'sending') {
      return
    }
    setStatus('sending')
    setFieldErrors({})
    setFailure(null)
    try {
      await sendEnquiry(product.id, values)
      setValues({ name: '', email: '', message: '' })
      setStatus('editing')
      setThanked(true)
    } catch (error) {
      setStatus('editing')
      if (error instanceof EnquiryRejected) {
        setFieldErrors(error.errors)
      } else {
        setFailure(error instanceof EnquiryNotSent ? error.message : new EnquiryNotSent().message)
      }
    }
  }

  return (
    <>
      <form className="enquiry" aria-label={`Enquire about ${product.title}`} onSubmit={submit} noValidate>
        {fields.map(({ name, label }) => {
          const messages = fieldErrors[name]
          const errorId = `enquiry-${name}-error`
          const props = {
            id: `enquiry-${name}`,
            name,
            value: values[name],
            onChange: (event: { target: { value: string } }) => {
              const { value } = event.target
              setValues((current) => ({ ...current, [name]: value }))
            },
            'aria-invalid': messages ? true : undefined,
            'aria-describedby': messages ? errorId : undefined,
          }
          return (
            <div key={name} className="field">
              <label htmlFor={props.id}>{label}</label>
              {name === 'message' ? (
                <textarea rows={5} {...props} />
              ) : (
                <input type={name === 'email' ? 'email' : 'text'} {...props} />
              )}
              {messages && (
                <p id={errorId} className="field-error">
                  {messages.join(' ')}
                </p>
              )}
            </div>
          )
        })}
        {failure && <p role="alert">{failure}</p>}
        <button type="submit" disabled={status === 'sending'}>
          {status === 'sending' ? 'Sending…' : 'Send enquiry'}
        </button>
      </form>
      {thanked && (
        <div role="status" className="toast">
          <span>Thank you for your enquiry.</span>
          <button type="button" aria-label="Dismiss" onClick={() => setThanked(false)}>
            ×
          </button>
        </div>
      )}
    </>
  )
}
