import { useCallback } from 'react'
import { useParams } from 'react-router-dom'
import { getCategoryProducts } from '../api/client'
import NotFound from '../components/NotFound'
import ProductTile from '../components/ProductTile'
import Unavailable from '../components/Unavailable'
import { useLoad } from '../useLoad'

function CategoryProducts({ name }: { name: string }) {
  const fetchProducts = useCallback(() => getCategoryProducts(name), [name])
  const [products, reload] = useLoad(fetchProducts)

  if (products.status === 'loading') {
    return <p>Loading…</p>
  }
  if (products.status === 'not-found') {
    return <NotFound message="We couldn't find that category." />
  }
  if (products.status === 'unavailable') {
    return <Unavailable onRetry={reload} />
  }

  // The backend decides the order: featured products first, then the rest.
  return (
    <section>
      <h1>{name}</h1>
      <div className="tiles">
        {products.data.map((product) => (
          <ProductTile key={product.id} product={product} />
        ))}
      </div>
    </section>
  )
}

export default function CategoryPage() {
  const { name = '' } = useParams()
  // Keyed by name so moving between categories always starts from a fresh load.
  return <CategoryProducts key={name} name={name} />
}
