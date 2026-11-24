// The only place in the frontend that knows how to reach the backend.
// Every request goes to our own /api, never to a third-party product source.

export type Health = { status: string }

export async function getHealth(): Promise<Health> {
  const response = await fetch('/api/health')
  if (!response.ok) {
    throw new Error(`Backend responded with ${response.status}`)
  }
  return (await response.json()) as Health
}
