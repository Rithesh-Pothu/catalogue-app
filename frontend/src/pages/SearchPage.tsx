import { useCallback } from 'react'
import { useLocation, useSearchParams } from 'react-router-dom'
import { searchProducts } from '../api/client'
import ProductTile from '../components/ProductTile'
import Unavailable from '../components/Unavailable'
import { useLoad } from '../useLoad'

function Results({ term }: { term: string }) {
  const fetchResults = useCallback(() => searchProducts(term), [term])
  const [results, reload] = useLoad(fetchResults)

  if (results.status === 'loading') {
    return <p role="status">Loading…</p>
  }
  if (results.status !== 'ready') {
    return <Unavailable onRetry={reload} />
  }

  return (
    <section>
      <h1>Results for "{term}"</h1>
      {results.data.length === 0 ? (
        <p role="status">Nothing found for "{term}".</p>
      ) : (
        <div className="tiles">
          {results.data.map((product) => (
            <ProductTile key={product.id} product={product} />
          ))}
        </div>
      )}
    </section>
  )
}

export default function SearchPage() {
  const [params] = useSearchParams()
  const { key } = useLocation()
  const term = (params.get('q') ?? '').trim()

  // A blank term is a prompt, not a search: nothing is asked of the backend.
  if (term === '') {
    return <p role="status">Enter a search term to find products.</p>
  }
  // Keyed by the visit, not just the term, so submitting the same term again
  // searches again (useful after the products were unavailable).
  return <Results key={key} term={term} />
}
