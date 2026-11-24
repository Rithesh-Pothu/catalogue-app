import { useCallback, useEffect, useState } from 'react'
import { NotFound } from './api/client'

export type Load<T> =
  | { status: 'loading' }
  | { status: 'ready'; data: T }
  | { status: 'not-found' }
  | { status: 'unavailable' }

/** Runs `fetcher` on mount and again whenever `reload` is called. */
export function useLoad<T>(fetcher: () => Promise<T>): [Load<T>, () => void] {
  const [state, setState] = useState<Load<T>>({ status: 'loading' })
  const [attempt, setAttempt] = useState(0)

  useEffect(() => {
    let cancelled = false
    fetcher().then(
      (data) => {
        if (!cancelled) setState({ status: 'ready', data })
      },
      (error: unknown) => {
        if (!cancelled) {
          setState({ status: error instanceof NotFound ? 'not-found' : 'unavailable' })
        }
      },
    )
    return () => {
      cancelled = true
    }
  }, [fetcher, attempt])

  const reload = useCallback(() => {
    setState({ status: 'loading' })
    setAttempt((count) => count + 1)
  }, [])

  return [state, reload]
}
