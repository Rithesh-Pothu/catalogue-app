import { Link } from 'react-router-dom'
import type { Product } from '../api/client'
import { formatPrice } from '../format'
import FeaturedBadge from './FeaturedBadge'

export default function ProductTile({ product }: { product: Product }) {
  return (
    <article className="tile">
      <Link to={`/products/${product.id}`}>
        {product.featured && <FeaturedBadge />}
        <img src={product.image} alt={product.title} loading="lazy" />
        <h3>{product.title}</h3>
        <p className="price">{formatPrice(product.price)}</p>
      </Link>
    </article>
  )
}
