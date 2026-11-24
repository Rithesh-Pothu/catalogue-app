import { Link, Outlet } from 'react-router-dom'
import { getCategories } from '../api/client'
import type { Load } from '../useLoad'
import { useLoad } from '../useLoad'
import CategoryLinks from './CategoryLinks'
import SearchBar from './SearchBar'

export type LayoutContext = {
  categories: Load<string[]>
  reloadCategories: () => void
}

/** The header with category links on every page, around the page itself. */
export default function Layout() {
  const [categories, reloadCategories] = useLoad(getCategories)
  const context: LayoutContext = { categories, reloadCategories }

  return (
    <>
      <header>
        <Link to="/" className="brand">
          Product catalogue
        </Link>
        {categories.status === 'ready' && (
          <nav aria-label="Categories">
            <CategoryLinks categories={categories.data} />
          </nav>
        )}
        <SearchBar />
      </header>
      <main>
        <Outlet context={context} />
      </main>
    </>
  )
}
