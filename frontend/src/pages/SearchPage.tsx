import { useCallback } from 'react'
import { useSearchParams } from 'react-router-dom'
import { searchProducts } from '../api/client'
import ProductTile from '../components/ProductTile'
import Unavailable from '../components/Unavailable'
import { useLoad } from '../useLoad'

function Results({ term }: { term: string }) {
  const fetchResults = useCallback(() => searchProducts(term), [term])
  const [results, reload] = useLoad(fetchResults)

  if (results.status === 'loading') {
    return <p>Loading…</p>
  }
  if (results.status !== 'ready') {
    return <Unavailable onRetry={reload} />
  }

  return (
    <section>
      <h1>Results for "{term}"</h1>
      {results.data.length === 0 ? (
        <p>Nothing found for "{term}".</p>
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
  const term = (params.get('q') ?? '').trim()

  // A blank term is a prompt, not a search: nothing is asked of the backend.
  if (term === '') {
    return <p>Enter a search term to find products.</p>
  }
  // Keyed by term so a new search always starts from a fresh load.
  return <Results key={term} term={term} />
}
