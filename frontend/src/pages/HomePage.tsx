import { useOutletContext } from 'react-router-dom'
import { getFeaturedProducts } from '../api/client'
import CategoryLinks from '../components/CategoryLinks'
import type { LayoutContext } from '../components/Layout'
import ProductTile from '../components/ProductTile'
import Unavailable from '../components/Unavailable'
import { useLoad } from '../useLoad'

export default function HomePage() {
  const { categories, reloadCategories } = useOutletContext<LayoutContext>()
  const [featured, reloadFeatured] = useLoad(getFeaturedProducts)

  const retry = () => {
    reloadFeatured()
    if (categories.status === 'unavailable') reloadCategories()
  }

  if (featured.status === 'loading') {
    return <p>Loading…</p>
  }
  if (featured.status === 'unavailable') {
    return <Unavailable onRetry={retry} />
  }

  return (
    <>
      <section aria-labelledby="featured-heading">
        <h2 id="featured-heading">Featured products</h2>
        <div className="tiles">
          {featured.data.map((product) => (
            <ProductTile key={product.id} product={product} />
          ))}
        </div>
      </section>
      {categories.status === 'ready' && (
        <section aria-labelledby="categories-heading">
          <h2 id="categories-heading">Browse by category</h2>
          <CategoryLinks categories={categories.data} />
        </section>
      )}
    </>
  )
}
