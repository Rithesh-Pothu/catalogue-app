import { Link } from 'react-router-dom'

export default function NotFound({ message }: { message: string }) {
  return (
    <div className="not-found">
      <p>{message}</p>
      <Link to="/">Back to the home page</Link>
    </div>
  )
}
