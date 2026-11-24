import { Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import CategoryPage from './pages/CategoryPage'
import HomePage from './pages/HomePage'
import NotFoundPage from './pages/NotFoundPage'
import ProductPage from './pages/ProductPage'

function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<HomePage />} />
        <Route path="products/:id" element={<ProductPage />} />
        <Route path="category/:name" element={<CategoryPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  )
}

export default App
