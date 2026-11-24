import { useState, type FormEvent } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'

/** In the header of every page. Submitting goes to the results page for the term. */
export default function SearchBar() {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const current = params.get('q') ?? ''
  // Starts from the term in the address, and is reset when that term changes.
  return <SearchForm key={current} initial={current} onSearch={(term) => navigate(`/search?q=${encodeURIComponent(term)}`)} />
}

function SearchForm({ initial, onSearch }: { initial: string; onSearch: (term: string) => void }) {
  const [term, setTerm] = useState(initial)

  function submit(event: FormEvent) {
    event.preventDefault()
    onSearch(term.trim())
  }

  return (
    <form role="search" className="search" onSubmit={submit}>
      <input
        type="search"
        aria-label="Search products"
        placeholder="Search products"
        value={term}
        onChange={(event) => setTerm(event.target.value)}
      />
      <button type="submit">Search</button>
    </form>
  )
}
