import { useCallback } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getProduct } from '../api/client'
import NotFound from '../components/NotFound'
import Unavailable from '../components/Unavailable'
import { categoryPath, formatPrice } from '../format'
import { useLoad } from '../useLoad'

function ProductDetails({ id }: { id: string }) {
  const fetchProduct = useCallback(() => getProduct(id), [id])
  const [product, reload] = useLoad(fetchProduct)

  if (product.status === 'loading') {
    return <p>Loading…</p>
  }
  if (product.status === 'not-found') {
    return <NotFound message="We couldn't find that product." />
  }
  if (product.status === 'unavailable') {
    return <Unavailable onRetry={reload} />
  }

  const { data } = product
  return (
    <article className="product">
      <img src={data.image} alt={data.title} />
      <div>
        <h1>{data.title}</h1>
        <p className="price">{formatPrice(data.price)}</p>
        {data.rating !== null && <p>Rating: {data.rating}</p>}
        <p>
          <Link to={categoryPath(data.category)}>{data.category}</Link>
        </p>
        <p>{data.description}</p>
        <button type="button">Enquire</button>
      </div>
    </article>
  )
}

export default function ProductPage() {
  const { id = '' } = useParams()
  // Keyed by id so moving between products always starts from a fresh load.
  return <ProductDetails key={id} id={id} />
}
