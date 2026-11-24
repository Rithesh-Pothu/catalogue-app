# Enquiry email goes through a replaceable provider

The email provider is expected to change, and reviewers cannot be asked to create paid accounts. The backend sends enquiries through a mail-provider interface, and locally the provider delivers to Mailpit over SMTP so the sales inbox can be read at `http://localhost:8025`. We rejected a vendor-specific client in the enquiry code and a plain log file as the default, because the first locks in one vendor and the second does not show what a real inbox receives.
