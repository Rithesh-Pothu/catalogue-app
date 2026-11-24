const dollars = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' })

export const formatPrice = (price: number) => dollars.format(price)

export const categoryPath = (category: string) => `/category/${encodeURIComponent(category)}`
