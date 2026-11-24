# 01: Project skeleton runs with one command

**What to build:** A reviewer runs one command and gets the React app, the Laravel backend and Mailpit running locally. The React app calls a backend endpoint through its API client and shows the result, which proves the browser only reaches data through our backend. Test runners work on both sides.

**Blocked by:** None (can start immediately).

**Status:** done

- [x] `docker compose up` starts the React app, the backend and Mailpit with no paid accounts
- [x] The React app shows a result fetched from the backend through a single API client, with no direct call to Fake Store
- [x] An example env file is committed and no secrets are committed
- [x] The backend test runner and the frontend test runner each run one passing test
- [x] Mailpit's inbox is reachable in a browser
