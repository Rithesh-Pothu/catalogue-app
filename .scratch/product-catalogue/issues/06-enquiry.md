# 06: Enquiry

**What to build:** An enquirer clicks Enquire on a product, enters their name, email address and a message, and submits. The sales inbox receives one email naming the product with those details, and the enquirer sees a Thank you. Invalid input stays on the form with the mistake shown and nothing is sent. A failed send shows an error, never a Thank you. This slice introduces the Enquiries module and the mail provider seam (ADR 0002).

**Blocked by:** 05: Product page.

**Status:** done

- [x] Clicking Enquire shows a form for name, email address and message for that product
- [x] A valid submit sends one email to the sales inbox naming the product and including the name, email address and message
- [x] A valid submit shows a Thank you message
- [x] The enquirer receives no email
- [x] A blank name, a blank message, or an invalid email address is rejected by the backend, shows the field errors, keeps what was typed, and sends nothing
- [x] When sending fails, an error is shown and never a Thank you
- [x] Only the backend's 201 reply counts as sent; any other reply shows the error
- [x] An enquiry for an unknown product is not sent
- [x] The mail provider has a Laravel adapter (SMTP to Mailpit locally, set by `MAIL_MAILER`) and an in-memory adapter
- [x] Backend request-level tests cover a valid enquiry, each invalid field, a failed send and an unknown product
- [x] Frontend component tests cover the invalid, sending, thank-you and error states with the API client mocked
- [x] The sent email can be read in Mailpit
