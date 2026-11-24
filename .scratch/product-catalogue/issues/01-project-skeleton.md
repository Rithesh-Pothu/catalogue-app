# 01: Project skeleton runs with one command

**What to build:** A reviewer runs one command and gets the React app, the Laravel backend and Mailpit running locally. The React app calls a backend endpoint through its API client and shows the result, which proves the browser only reaches data through our backend. Test runners work on both sides.

**Blocked by:** None (can start immediately).

**Status:** ready-for-agent

- [ ] `docker compose up` starts the React app, the backend and Mailpit with no paid accounts
- [ ] The React app shows a result fetched from the backend through a single API client, with no direct call to Fake Store
- [ ] An example env file is committed and no secrets are committed
- [ ] The backend test runner and the frontend test runner each run one passing test
- [ ] Mailpit's inbox is reachable in a browser
