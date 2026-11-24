import { Link } from 'react-router-dom'
import { categoryPath } from '../format'

export default function CategoryLinks({ categories }: { categories: string[] }) {
  return (
    <ul className="category-links">
      {categories.map((category) => (
        <li key={category}>
          <Link to={categoryPath(category)}>{category}</Link>
        </li>
      ))}
    </ul>
  )
}
