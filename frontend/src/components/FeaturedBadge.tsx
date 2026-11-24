/** A star and the word "Featured", for products the shop is highlighting. */
export default function FeaturedBadge() {
  return (
    <span className="featured-badge">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3 6.1 20.6l1.3-6.6L2.5 9.4l6.6-.8z" />
      </svg>
      Featured
    </span>
  )
}
