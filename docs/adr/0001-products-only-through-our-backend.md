# Products reach the browser only through our backend

The challenge forbids calling the Fake Store API from the client, and the product source is expected to change. The React app talks only to our Laravel backend. The backend reads products through a single product-source interface, with Fake Store as the first implementation, and returns our own product shape, so a new source means one new implementation and no frontend change. We rejected letting the client call the source directly, because it would tie the UI to one vendor's data shape.
