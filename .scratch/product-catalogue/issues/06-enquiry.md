# 06: Enquiry

**What to build:** An enquirer clicks Enquire on a product, enters their name, email address and a message, and submits. The sales inbox receives one email naming the product with those details, and the enquirer sees a Thank you. Invalid input stays on the form with the mistake shown and nothing is sent. A failed send shows an error, never a Thank you. This slice introduces the Enquiries module and the mail provider seam (ADR 0002).

**Blocked by:** 05: Product page.

**Status:** ready-for-agent

- [ ] Clicking Enquire shows a form for name, email address and message for that product
- [ ] A valid submit sends one email to the sales inbox naming the product and including the name, email address and message
- [ ] A valid submit shows a Thank you message
- [ ] The enquirer receives no email
- [ ] A blank name, a blank message, or an invalid email address is rejected by the backend, shows the field errors, keeps what was typed, and sends nothing
- [ ] When sending fails, an error is shown and never a Thank you
- [ ] An enquiry for an unknown product is not sent
- [ ] The mail provider has an SMTP adapter, delivering to Mailpit locally, and an in-memory adapter
- [ ] Backend request-level tests cover a valid enquiry, each invalid field, a failed send and an unknown product
- [ ] Frontend component tests cover the invalid, sending, thank-you and error states with the API client mocked
- [ ] The sent email can be read in Mailpit
