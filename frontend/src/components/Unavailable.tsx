export default function Unavailable({ onRetry }: { onRetry: () => void }) {
  return (
    <div role="alert" className="unavailable">
      <p>Products are unavailable right now.</p>
      <button type="button" onClick={onRetry}>
        Try again
      </button>
    </div>
  )
}
