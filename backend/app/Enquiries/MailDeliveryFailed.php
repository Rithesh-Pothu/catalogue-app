<?php

namespace App\Enquiries;

use RuntimeException;

/**
 * The mail provider could not deliver an email. The API answers 502 (see the
 * exception handler in bootstrap/app.php), so an enquiry that was not sent is
 * never reported as sent.
 */
final class MailDeliveryFailed extends RuntimeException {}
