import { useEffect, useState } from 'react'
import { getHealth } from './api/client'

type BackendState = 'checking' | 'ok' | 'unreachable'

function App() {
  const [backend, setBackend] = useState<BackendState>('checking')

  useEffect(() => {
    getHealth()
      .then((health) => setBackend(health.status === 'ok' ? 'ok' : 'unreachable'))
      .catch(() => setBackend('unreachable'))
  }, [])

  return (
    <main>
      <h1>Product catalogue</h1>
      <p>Backend: {backend}</p>
    </main>
  )
}

export default App
