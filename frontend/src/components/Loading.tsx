/** A backdrop over the whole page with a spinner, shown while products load. */
export default function Loading() {
  return (
    <div className="backdrop" role="status" aria-label="Loading products">
      <div className="spinner" aria-hidden="true" />
    </div>
  )
}
